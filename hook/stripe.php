<?php
/**
 * 
 *  pour recevoir les hooks de stripe en mode prof
 *  Voil� les hooks re�us :
 *  - invoice.payment_succeeded pour transf�rer les fonds au prof
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

\Stripe\Stripe::setApiKey(PROD_SECRET_KEY_STRP);

$input = @file_get_contents("php://input");

$event_json = json_decode($input);

if ($event_json->type == "invoice.payment_succeeded") {

    $stripeMg = new \spamtonprof\stp_api\StripeManager(false);

    $custom_fields = $event_json->data->object->custom_fields;
    $email_prof = false;
    foreach ($custom_fields as $custom_field) {
        if ($custom_field->name == 'email_prof') {
            $email_prof = $custom_field->value;
        }
    }

    if ($email_prof) {

        $stripeMg->transfert_custom_facture($event_json, $email_prof);
    } else {
        $stripeMg->transfertSubscriptionCharge($event_json);
    }
}


