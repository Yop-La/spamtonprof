<?php
/*
 * cron de récupération des transactions des profs
 * pas encore en prod
 *
 */
require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-config.php");
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('PROBLEME_CLIENT', true);

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('stripe_transactions', array(
    '------',
    'Debut de récupération des transations'
));

$test_mode = false;

$stripePayoutMg = new \spamtonprof\stp_api\StripePayoutManager();
$stripeTransactionMg = new \spamtonprof\stp_api\StripeTransactionManager();
$stripeChargeManagerMg = new \spamtonprof\stp_api\StripeChargeManager();

$profMg = new \spamtonprof\stp_api\StpProfManager();

$payouts = $stripePayoutMg->getAll(array(
    'key' => 'no_transactions_status'
));

if (count($payouts) == 0) {
    $slack->sendMessages('stripe_transactions', array(
        'Aucun payout à traiter ...'
    ));
}

foreach ($payouts as $payout) {

    $prof = $profMg->get(array(
        'ref_prof' => $payout->getRef_prof()
    ));

    $stripe_prof = new \spamtonprof\stp_api\StripeManager($test_mode, $prof->getEmail_stp());

    $all_transactions = $stripe_prof->list_balance_transaction($payout->getRef_stripe());

    $nb_transactions = 0;

    if ($all_transactions) {
        $nb_transactions = count($all_transactions);
    }
    $slack->sendMessages('stripe_transactions', array(
        $nb_transactions . " à traiter pour le payout: " . $payout->getRef_stripe()
    ));

    if (! $all_transactions || $nb_transactions == 0) {

        $slack->sendMessages('stripe_transactions', array(
            '-->>    Impossible de récupérer les transactions de ce payout:' . $payout->getRef_stripe() . "    <<--"
        ));

        $payout->setTransactions_status($payout::cant_retrieve_transactions);
        $stripePayoutMg->update_transactions_status($payout);
        continue;
    }

    $payout->setTransactions_status($payout::not_all_transactions_retrieved);
    $stripePayoutMg->update_transactions_status($payout);

    foreach ($all_transactions as $transaction) {

        $transaction = $stripe_prof->retrieve_balance_transaction($transaction->id);

        $available_on = new \DateTime();
        $available_on->setTimestamp($transaction->available_on);

        $stripeTransaction = new \spamtonprof\stp_api\StripeTransaction(array(
            "transaction_id" => $transaction->id,
            "transaction_amount" => $transaction->amount,
            "ref_payout" => $payout->getRef(),
            "test_mode" => $test_mode,
            "available_on" => $available_on->format(PG_DATETIME_FORMAT),
            "type" => $transaction->type
        ));

        $stripeTransactionInBase = $stripeTransactionMg->get(array(
            'key' => 'transaction_id',
            'params' => array(
                'transaction_id' => $stripeTransaction->getTransaction_id()
            )
        ));

        if ($stripeTransactionInBase) {
            $stripeTransaction = $stripeTransactionInBase;
        } else {
            $stripeTransactionMg->add($stripeTransaction);
        }

        $charge = $stripe_prof->retrieve_source_charge_of_transaction($transaction->id);
        if ($charge) {

            $created = new \DateTime();
            $created->setTimestamp($charge->created);

            $stripeCharge = new \spamtonprof\stp_api\StripeCharge(array(
                "ref_stripe" => $charge->id,
                "amount" => $charge->amount,
                "created" => $created->format(PG_DATETIME_FORMAT),
                "customer" => $charge->customer,
                'invoice' => $charge->invoice
            ));

            $stripeChargeInBase = $stripeChargeManagerMg->get(array(
                'key' => 'ref_stripe',
                'params' => array(
                    'ref_stripe' => $stripeCharge->getRef_stripe()
                )
            ));

            if ($stripeChargeInBase) {

                $stripeCharge = $stripeChargeInBase;
            } else {
                $stripeCharge = $stripeChargeManagerMg->add($stripeCharge);
            }

            $stripeTransaction->setRef_charge($stripeCharge->getRef());
            $stripeTransactionMg->update_ref_charge($stripeTransaction);
        }
    }

    $payout->setTransactions_status($payout::transactions_retrieved);
    $stripePayoutMg->update_transactions_status($payout);
}

$slack->sendMessages('stripe_transactions', array(
    'Fin de récupération des transations'
));

exit();