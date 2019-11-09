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


$slack = new \spamtonprof\slack\Slack();


$message = explode("-",$_POST["message"]);
$channel = $_POST["channel"];

$slack = new \spamtonprof\slack\Slack();
$slack -> sendMessages($channel, $message);

