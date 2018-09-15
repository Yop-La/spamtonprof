<?php
use spamtonprof\stp_api\AccountManager;
use spamtonprof\stp_api\EleveManager;
use spamtonprof\stp_api\ClasseManager;
use spamtonprof\stp_api\GmailLabelManager;
use spamtonprof\stp_api\GmailLabel;
use spamtonprof\stp_api\NbEmailManager;
use spamtonprof\stp_api\GetResponseManager;
use spamtonprof\getresponse_api\CampaignManager;

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

/*
 * ce script retourne les comptes lbc à checker par zennoposter
 * Il se contente de retourner une liste de (ref_compte , code promo ) des comptes à checker
 * Les comptes à checker sont les comptes :
 * - avec des annonces :
 * - dont l'état est 'enAttenteModeration'
 * - dont la date de publication est supérieur ou égale au 30/05/2018 ( date de mise en place des codes promo )
 *
 * Ce script retourne en priorité les comptes avec des annonces plus anciennes
 *
 *
 */

$lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

if (count($_GET) != 0) {
    
    $nbCompte = $_GET["nbCompte"];
    
    $lbcAccountMg->desactivateDeadAccounts();
    
    $accounts = $lbcAccountMg->getAccountToScrap($nbCompte);
    
    $retour = new \stdClass();
    $retour->accounts = $accounts;
    
    prettyPrint($retour);
} else if (count($_POST) != 0) {
    
    $obj = urldecode($_POST["accounts"]);
    
    $rows = explode("\r\n", $obj);
    
    $nbTot = $lbcAccountMg->updateAfterScraping($rows);
    
    $slack = new \spamtonprof\slack\Slack();
    $slack->sendMessages("log", array(
        "contrôles des annonces réalisés",
        "Il y a au moins " . $nbTot . " en ligne"
    ));
}

