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
$slack->sendMessages('stripe_invoices', array(
    '------',
    'Debut de récupération des invoices'
));

$test_mode = false;

$stripeChargeManagerMg = new \spamtonprof\stp_api\StripeChargeManager();
$invoiceMg = new \spamtonprof\stp_api\StripeInvoiceManager();

$stripe = new \spamtonprof\stp_api\StripeManager($test_mode);

$charges = $stripeChargeManagerMg->getAll(array(
    'key' => 'ref_invoice_is_null'
));

if (count($charges) == 0) {
    $slack->sendMessages('stripe_invoices', array(
        'Aucune charge à traiter ...'
    ));
}

$messages = [];

foreach ($charges as $charge) {

    if (count($messages) == 10) {

        $slack->sendMessages('stripe_invoices', $messages);
        $messages = [];
    }

    $invoice = $stripe->retrieve_invoice($charge->getInvoice());

    $messages[] = 'invoice ' . $invoice->id . ' en traitement';

    $period_end = new \DateTime();
    $period_end->setTimestamp($invoice->period_end);

    $period_start = new \DateTime();
    $period_start->setTimestamp($invoice->period_start);

    $invoice = $invoiceMg->add(new \spamtonprof\stp_api\StripeInvoice(array(
        'ref_stripe' => $invoice->id,
        'period_end' => $period_end->format(PG_DATETIME_FORMAT),
        'period_start' => $period_start->format(PG_DATETIME_FORMAT),
        'subscription' => $invoice->subscription,
        'description' => $invoice->lines->data[0]->description,
        'amount_paid' => $invoice->amount_paid,
        'amount_due' => $invoice->amount_due,
        'customer_email' => $invoice->customer_email,
        'customer' => $invoice->customer
    )));

    $charge->setRef_invoice($invoice->getRef());
    $stripeChargeManagerMg->update_ref_invoice($charge);
}

if (count($messages) != 0) {

    $slack->sendMessages('stripe_invoices', $messages);
    $messages = [];
}

$slack->sendMessages('stripe_invoices', array(
    '------',
    'Fin de récupération des invoices'
));

exit();