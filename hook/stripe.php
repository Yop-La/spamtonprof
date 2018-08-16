<?php
/**
 * 
 *  pour recevoir les hooks de stripe en mode prof
 *  Voilà les hooks reçus :
 *  - invoice.payment_succeeded pour transférer les fonds au prof
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

$slack = new \spamtonprof\slack\Slack();

\Stripe\Stripe::setApiKey(TEST_SECRET_KEY_STRP);

$input = @file_get_contents("php://input");

$event_json = json_decode($input);

if ($event_json->type == "invoice.payment_succeeded") {
    
    $stripeMg = new \spamtonprof\stp_api\StripeManager(false);
    
    $stripeMg->transfertSubscriptionCharge($event_json);
    
}


