<?php
use spamtonprof\stp_api\AccountManager;
use spamtonprof\stp_api\EleveManager;
use spamtonprof\stp_api\ClasseManager;
use spamtonprof\stp_api\GmailLabelManager;
use spamtonprof\stp_api\GmailLabel;
use spamtonprof\stp_api\NbEmailManager;
use spamtonprof\stp_api\GetResponseManager;
use spamtonprof\getresponse_api\CampaignManager;
use spamtonprof\stp_api\LbcProcessManager;
use Hashids\Hashids;

require_once (dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_reporting(- 1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/**
 * appelé par un template zenno lorsque qu'une annonce n'est pas reconnu par leboncoin
 * le script se charge de mettre la colonne lbc à true ne pas sélectionner lors de prochaines publications
 */
$commune = $_POST['commune'];

$commune = explode(" ", $commune);

$codePostal = $commune[count($commune) - 1];

unset($commune[count($commune) - 1]);
$commune = implode(" ", $commune);

$lbcCommuneMg = new \spamtonprof\stp_api\LbcCommuneManager();

$lbcCommune = $lbcCommuneMg->get(array(
    "code_postal" => trim($codePostal),
    "libelle" => trim($commune)
));
$lbcCommune->setLbc(true);
$lbcCommuneMg->updateLbc($lbcCommune);
