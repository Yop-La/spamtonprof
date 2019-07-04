<?php
/**
 * 
 *  pour generer des annonces lbc avant publication par zenno
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

// recuperation des entrees

serializeTemp($_POST);

$nbAds = $_POST["nb_ads"];
$refClient = $_POST["ref_client"];
$phone = $_POST["phone"];
$refCompte = $_POST["ref_compte"];

$code_promo = false;
if (array_key_exists("code_promo", $_POST)) {
    $code_promo = $_POST["code_promo"];
}

$lbcProcessMg = new \spamtonprof\stp_api\LbcProcessManager();

$clientMg = new \spamtonprof\stp_api\LbcClientManager();
// on recupere le client
$client = $clientMg->get(array(
    'ref_client' => $refClient
));

$now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

$lbc_campaign_mg = new \spamtonprof\stp_api\LbcCampaignManager();
$lbc_campaign = $lbc_campaign_mg->add(new \spamtonprof\stp_api\LbcCampaign(array(
    "date" => $now->format(PG_DATETIME_FORMAT),
    "ref_compte" => $refCompte,
    "nb_ad_online" => 0,
    "nb_ad_publie" => 0
)));


$category = $client->getCategory();
$ads = $lbcProcessMg->generateAds($refClient, $nbAds, $phone, true, $refCompte, $lbc_campaign);


$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log-lbc', array(
    "LBC : publication sur le compte de ref_compte_lbc : " . $refCompte,
    'params',
    json_encode($_POST)
));

$retour = new stdClass();
$retour->ads = $ads;
$retour->category = $category;
prettyPrint($retour);