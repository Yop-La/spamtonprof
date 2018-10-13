<?php
/**
 * 
 *  pour générer des annonces lbc avant publication par zenno
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


// récupération des entrées

serializeTemp($_POST);

$slack = new \spamtonprof\slack\Slack();

$slack -> sendMessages("log", array("test"));

$nbAds = $_POST["nb_ads"];
$refClient = $_POST["ref_client"];
$phone = $_POST["phone"];
$refCompte = $_POST["ref_compte"];

$lbcProcessMg = new \spamtonprof\stp_api\LbcProcessManager();

$ads = $lbcProcessMg -> generateAds($refClient, $nbAds, $phone, $refCompte);

$retour = new stdClass();
$retour->ads = $ads;
prettyPrint($retour);