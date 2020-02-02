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

$nb_payout = 5;

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('push_full_payout', array(
    '------',
    'Start to get ' . $nb_payout . ' full payouts'
));

$algolia = new \spamtonprof\stp_api\AlgoliaManager();

$constructor = array(
    "construct" => array(
        'transactions'
    ),
    "transactions" => array(
        "construct" => array(
            'ref_charge'
        ),
        'ref_charge' => array(
            "construct" => array(
                'ref_abo',
                'ref_invoice'
            ),
            'ref_abo' => array(
                "construct" => array(
                    'ref_eleve',
                    'ref_parent'
                )
            )
        )
    )
);

$stripePayoutMg = new \spamtonprof\stp_api\StripePayoutManager();
$stripeTransactionMg = new \spamtonprof\stp_api\StripeTransactionManager();
$stripeChargeManagerMg = new \spamtonprof\stp_api\StripeChargeManager();
$stripeInvoiceManagerMg = new \spamtonprof\stp_api\StripeInvoiceManager();
$profMg = new \spamtonprof\stp_api\StpProfManager();

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$payout = $stripePayoutMg->get(array(
    'key' => 'payout_to_push_in_algolia'
), $constructor);

if (! $payout) {

    $slack->sendMessages('push_full_payout', array(
        'End: no payout to process ...'
    ));
    exit();
}

$messages = [];

$nb_transactions = 0;

for ($i = 0; $i < $nb_payout; $i ++) {
    
    $messages[] = '--';
    $messages[] = 'Payout n° ' .( $i+1 );

    if (count($messages) == 20) {

        $slack->sendMessages('push_full_payout', $messages);
        $messages = [];
    }

    $payout = $stripePayoutMg->get(array(
        'key' => 'payout_to_push_in_algolia'
    ), $constructor);

    $prof = $profMg->get(array(
        'ref_prof' => $payout->getRef_prof()
    ));

    $payout->setTransactions_status("pushing_to_algolia");
    $stripePayoutMg->update_transactions_status($payout);

    $messages[] = 'Payout ' . $payout->getRef_stripe() . ' récupéré';

    $transactions = $payout->getTransactions();

    $fullTransaction = new \stdClass();

    foreach ($transactions as $transaction) {

        $transaction = $stripeTransactionMg->cast($transaction);

        if ($transaction->getType() == "payout_failure") {
            continue;
        }

        $charge = $transaction->getCharge();
        $charge = $stripeChargeManagerMg->cast($charge);

        $invoice = $charge->getInvoice();
        $invoice = $stripeInvoiceManagerMg->cast($invoice);

        $abo = $charge->getAbo();

        $emails = [];
        $prenom_eleve = 'aucun';
        if ($abo) {

            $abo = $aboMg->cast($abo);
            $eleve = $abo->getEleve();

            $proche = $abo->getProche();

            $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

            $emails[] = $eleve->getEmail();
            if ($proche) {
                $proche = \spamtonprof\stp_api\StpProche::cast($proche);
                $emails[] = $proche->getEmail();
            }

            $prenom_eleve = $eleve->getPrenom() . ' ' . $eleve->getNom();
        }

        $emails[] = $invoice->getCustomer_email();
        $emails = array_unique($emails);

        $date_versement = $payout->getDate_versement();
        $date_versement = \DateTime::createFromFormat(PG_DATETIME_FORMAT, $date_versement);
        
        $fullTransaction->payout_date = $date_versement->format(FR_DATE_FORMAT);
        $fullTransaction->payout_date_timestamp = $date_versement->getTimestamp();
        $fullTransaction->payout_amount = $payout->getAmount();
        $fullTransaction->ref_payout = $payout->getRef_stripe();

        $fullTransaction->emails = $emails;

        $fullTransaction->eleve = $prenom_eleve;

        $fullTransaction->transaction_type = $transaction->getType();
        $fullTransaction->ref_transaction = $transaction->getTransaction_id();
        
        $fullTransaction->start_week = $invoice->getPeriod_start();
        $fullTransaction->end_week = $invoice->getPeriod_end();

        $fullTransaction->total_amount = $charge->getAmount();
        $fullTransaction->paid_amount = $transaction->getTransaction_amount();
        $fullTransaction->commission_amount = intval($charge->getAmount()) - intval($transaction->getTransaction_amount());

        $fullTransaction->formule_name = $invoice->getDescription();
        $fullTransaction->invoice_id = $invoice->getRef_stripe();

        $fullTransaction->email_prof = $prof->getEmail_stp();
        
        $messages[] = $prof->getEmail_stp();

        $objectId = $payout->getRef_stripe() . "_" . $transaction->getTransaction_id();
        $algolia->addTransaction($fullTransaction, $objectId);

        $nb_transactions = $nb_transactions + 1;

        $messages[] = 'Transaction ' . $objectId . ' pushed in algolia';
    }

    $payout->setTransactions_status("full_payout_in_algolia");
    $stripePayoutMg->update_transactions_status($payout);
}

if (count($messages) != 0) {

    $slack->sendMessages('push_full_payout', $messages);
    $messages = [];
}
$slack->sendMessages('push_full_payout', array(
    '------',
    $nb_transactions . ' transactions pushed in algolia',
    'End of pushing full payout'
));

exit();

