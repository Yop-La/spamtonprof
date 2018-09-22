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
 * ce script est utilisé par le template check adds de zennoposter
 * Au début, ce template envoie une requête get pour avoir une liste des comptes à checker 
 * A la fin, il envoie une requête pour donner le résultat du contrôles des annonces (un tableau de code_promo, nb_annonces, ref_compte)
 * 
 * 
 * Etapes :
 *  - Etape 1 : le template zenno envoie une requete get :
 *      - cette requete get désactive les comptes sans code promo ( publication qui a échoué , ils ne seront plus contrôles) 
 *      - met à jour la date de contrôle des comptes sans code promo ( cette date ne sera plus jamais mis à jour ensuite )
 *      - elle retourne la liste des comptes à contrôler par zenno
 *  - Etape 2 : le template zenno contrôle les comptes données par la requête get ( les comptes sans code promo ne sont pas dans cette liste )
 *  - Etape 3 : le template zenno envoie une requete post qui contient le resultat de la publication array( code_promo, ref_compte, nb_annonces)
 *      - le script met ensuite à jour la date de contrôle de ces comptes
 *      - mise à jour du compte activé/désactivé
 *      - mise à jour du nombre d'annonces
 */



$lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

$slack = new \spamtonprof\slack\Slack();

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

    $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
    
    $algoliaMg -> updateReportingLbc();
    
    
    $slack->sendMessages("log", array(
        "contrôles des annonces réalisés",
        "Il y a au moins " . $nbTot . " en ligne"
    ));

}
