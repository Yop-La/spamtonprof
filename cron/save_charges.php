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
$slack->sendMessages('stripe_charges', array(
    '------',
    'Debut de récupération des charges'
));

$test_mode = false;

$stripePayoutMg = new \spamtonprof\stp_api\StripePayoutManager();
$stripeTransactionMg = new \spamtonprof\stp_api\StripeTransactionManager();
$stripeChargeManagerMg = new \spamtonprof\stp_api\StripeChargeManager();

$profMg = new \spamtonprof\stp_api\StpProfManager();

$transactions = $stripeTransactionMg->getAll(array(
    'key' => 'ref_charge_is_null'
));

if (count($transactions) == 0) {
    $slack->sendMessages('stripe_charges', array(
        'Aucune transactions à traiter ...'
    ));
}

$messages = [];

foreach ($transactions as $transaction) {

    if (count($messages) == 10) {

        $slack->sendMessages('stripe_charges', $messages);
        $messages = [];
    }

    $payout = $stripePayoutMg->get(array(
        'key' => 'ref',
        'params' => array(
            'ref' => $transaction->getRef_payout()
        )
    ));

    $prof = $profMg->get(array(
        'ref_prof' => $payout->getRef_prof()
    ));

    $stripe_prof = new \spamtonprof\stp_api\StripeManager($test_mode, $prof->getEmail_stp());

    $charge = $stripe_prof->retrieve_source_charge_of_transaction($transaction->getTransaction_id());

    $messages[] = 'Traitement de la transaction : ' . $transaction->getTransaction_id();

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

        $transaction->setRef_charge($stripeCharge->getRef());
        $stripeTransactionMg->update_ref_charge($transaction);
    } else {

        $messages[] = '->> Aucune charge pour cette transaction: ' . $transaction->getTransaction_id() . '<<-';
    }
}


if (count($messages) != 0) {
    
    $slack->sendMessages('stripe_charges', $messages);
    $messages = [];
}
$slack->sendMessages('stripe_charges', array(
    '------',
    'Fin de récupération des charges'
));

exit();