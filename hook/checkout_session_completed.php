<?php
/**
 * 
 *  pour recevoir les hooks de stripe 
 *  Voil� les hooks re�us :
 *  - checkout.session.completed pour créer un abonnement après le paiement
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

$input = @file_get_contents("php://input");

$event_json = json_decode($input);

serializeTemp($event_json);

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log', array(
    'test hook stripe',
    json_encode($event_json)
));

if ($event_json->type == "checkout.session.completed") {

    $live_mode = $event_json->livemode;
    $customer_id = $event_json->data->object->customer;
    $subscription_id = $event_json->data->object->subscription;

    if ($subscription_id) {

        $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
        $aboMg->activate_sub_after_checkout_sucess($subscription_id, $customer_id, $live_mode);
    }
}
