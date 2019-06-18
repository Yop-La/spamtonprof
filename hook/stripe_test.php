<?php
/**
 * 
 *  pour recevoir les hooks de stripe en mode test
 *  Voil� les hooks re�us :
 *  - invoice.payment_succeeded pour envoyer les fonds au prof
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

// pour tester le transfert de paiement
// # hook type de text (avec coupon) : https://dashboard.stripe.com/test/events/evt_1DhdtjIcMMHYXO98CO5yimer

\Stripe\Stripe::setApiKey(TEST_SECRET_KEY_STRP);

$input = @file_get_contents("php://input");

$event_json = json_decode($input);

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log', array(
    'test hook stripe',
    json_encode($event_json)
));

if ($event_json->type == "invoice.payment_succeeded") {

    $stripeMg = new \spamtonprof\stp_api\StripeManager(true);

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


