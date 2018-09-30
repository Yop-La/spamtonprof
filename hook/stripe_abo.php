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
$slack->sendMessages("log", array(
    "hook recu"
));

$input = @file_get_contents("php://input");

$event_json = json_decode($input);

$object = $event_json->data->object;

$algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();



switch ($event_json->type) {
    case "customer.subscription.created":
        $abo = new \spamtonprof\stripe\Subscription($object);
        $abo ->toAlgoliaFormat();
        $algoliaMg->addAbo($abo);
        break;
    case "customer.subscription.updated":
        $abo = new \spamtonprof\stripe\Subscription($object);
        $abo ->toAlgoliaFormat();
        $algoliaMg->updateAbo($abo);
        break;
    case "customer.subscription.deleted":
        $abo = new \spamtonprof\stripe\Subscription($object);
        $abo ->toAlgoliaFormat();
        $algoliaMg->updateAbo($abo);
        break;
}