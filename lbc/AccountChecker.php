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
 * ce script est utilis� par le template check adds de zennoposter
 * Au d�but, ce template envoie une requ�te get pour avoir une liste des comptes � checker 
 * A la fin, il envoie une requ�te pour donner le r�sultat du contr�les des annonces (un tableau de code_promo, nb_annonces, ref_compte)
 * 
 * 
 * Etapes :
 *  - Etape 1 : le template zenno envoie une requete get :
 *      - cette requete get d�sactive les comptes sans code promo ( publication qui a �chou� , ils ne seront plus contr�les) 
 *      - met � jour la date de contr�le des comptes sans code promo ( cette date ne sera plus jamais mis � jour ensuite )
 *      - elle retourne la liste des comptes � contr�ler par zenno
 *  - Etape 2 : le template zenno contr�le les comptes donn�es par la requ�te get ( les comptes sans code promo ne sont pas dans cette liste )
 *  - Etape 3 : le template zenno envoie une requete post qui contient le resultat de la publication array( code_promo, ref_compte, nb_annonces)
 *      - le script met ensuite � jour la date de contr�le de ces comptes
 *      - mise � jour du compte activ�/d�sactiv�
 *      - mise � jour du nombre d'annonces
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
        "contr�les des annonces r�alis�s",
        "Il y a au moins " . $nbTot . " en ligne"
    ));

}
