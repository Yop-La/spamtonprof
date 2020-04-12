<?php
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

// en prod. une fois par jour à 5h42
/*
 * pour donner des kpis sur les impayés au gestionnaire
 */

$stripeChargeFailedMg = new \spamtonprof\stp_api\StripeChargeFailedManager();
$stripeMg = new \spamtonprof\stp_api\StripeManager(false);

$all_invoices = [];

$starting_after = false;
for ($i = 0; $i < 5; $i ++) {

    $invoices = $stripeMg->list_invoices(100, $starting_after, "open");
    if (count($invoices) != 0) {
        $starting_after = $invoices[count($invoices) - 1]->id;
        $all_invoices = array_merge($all_invoices, $invoices);
    }
}

$nb_unpaid_invoice = count($all_invoices);

$nb_unpaid_notif = count($stripeChargeFailedMg->getAll());

$email = new \SendGrid\Mail\Mail();
$email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

$params = [
    'nb_charge_failed' => "".$nb_unpaid_notif,
    'nb_unpaid_invoice' => "".$nb_unpaid_invoice
];

try {

    $email->addTo("alexandre@spamtonprof.com", "Alexandre", $params, 0);
    
    $email->setTemplateId("d-5d4c23bdff0f45b3996715208aa620b7");
    $sendgrid = new \SendGrid(SEND_GRID_API_KEY);
    
    $response = $sendgrid->send($email);
    
    echo($response->statusCode());
    
    
} catch (Exception $e) {

echo($e->getMessage());
}




