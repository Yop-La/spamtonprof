<?php
/*
 * cron de génération de factures des commissions
 * génère, envoie et ferme les factures aux profs
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

$test_mode = true;

$stripe = new \spamtonprof\stp_api\StripeManager(false);

$stripeTest = new \spamtonprof\stp_api\StripeManager($test_mode);

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('invoice_gene', array(
    '------',
    'Starting to generate invoice'
));

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
    'key' => 'payout_with_no_prof_invoice',
    'params' => array(
        'test_mode' => $test_mode
    )
), $constructor);

if (! $payout) {

    $slack->sendMessages('invoice_gene', array(
        'End: no payout to process ...'
    ));
    exit();
}

$messages = [];

$nb_transactions = 0;

for ($i = 0; $i < 5; $i ++) {

    $stripeTest->delete_all_pending_invoice_items();

    // pour chaque transaction, créer une invoice item

    // finalement créer la facture

    if (count($messages) == 10) {

        $slack->sendMessages('invoice_gene', $messages);
        $messages = [];
    }

    $payout = $stripePayoutMg->get(array(
        'key' => 'payout_with_no_prof_invoice',
        'params' => array(
            'test_mode' => $test_mode
        )
    ), $constructor);

    $prof = $profMg->get(array(
        'ref_prof' => $payout->getRef_prof()
    ));

    if (! $prof->getCustomer($test_mode)) {

        $cus = $stripeTest->add_customer($prof->getEmail_stp());
        $prof->setCustomer($cus->id, $test_mode);

        $profMg->updateCustomer($prof, $test_mode);
    }

    $cus = $stripeTest->retrieve_customer($prof->getCustomer($test_mode));

    $invoice_prof = $payout->getInvoice_prof($test_mode);

    $invoice = false;
    if ($invoice_prof) {

        continue;
    }

    $messages[] = '--';
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

        $fullTransaction->payout_date = $payout->getDate_versement();
        $fullTransaction->payout_amount = $payout->getAmount();

        $fullTransaction->emails = $emails;

        $fullTransaction->eleve = $prenom_eleve;

        $fullTransaction->transaction_type = $transaction->getType();

        $fullTransaction->start_week = $invoice->getPeriod_start();
        $fullTransaction->end_week = $invoice->getPeriod_end();

        $fullTransaction->total_amount = $charge->getAmount();
        $fullTransaction->paid_amount = $transaction->getTransaction_amount();
        $fullTransaction->commission_amount = intval($charge->getAmount()) - intval($transaction->getTransaction_amount());

        $fullTransaction->formule_name = $invoice->getDescription();

        $fullTransaction->email_prof = $prof->getEmail_stp();

        $description = "Du " . substr($fullTransaction->start_week, 0, 10) . "-" . 'Pour ' . $fullTransaction->eleve . "-" . $fullTransaction->formule_name;

        $stripeTest->createInvoiceItem($cus->id, $fullTransaction->commission_amount, $description);

        $nb_transactions = $nb_transactions + 1;

        $messages[] = 'Line item created';
    }

    $metadata = [];
    $metadata['stripe_payout'] = $payout->getRef_stripe();

    $invoice = $stripeTest->createInvoice($cus->id, "Aucun paiement n'est attendue pour cette facture.", $metadata);

    $stripeTest->sendInvoice($invoice->id);

    $stripeTest->markUncollectible($invoice->id);

    $payout->setInvoice_prof($invoice->id, $test_mode);
    $stripePayoutMg->updateInvoiceProf($payout, $test_mode);

    $messages[] = 'Invoice for ' . $prof->getEmail_stp() . ' created:' . $invoice->id;
}

if (count($messages) != 0) {

    $slack->sendMessages('invoice_gene', $messages);
    $messages = [];
}

$slack->sendMessages('invoice_gene', array(
    '------',
    $nb_transactions . ' items invoiced',
    'End of invoice generation'
));

exit();
