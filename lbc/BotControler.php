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

/* espace de travail */
echo("deb");
$slack = new spamtonprof\slack\Slack();

$msgs = [];

$msgs[] =  "début du contrôle";



$lbcAccountMg = new spamtonprof\stp_api\LbcAccountManager();
$gmailManager = new spamtonprof\gmailManager\GmailManager("mailsfromlbc@gmail.com");

$addLbcMg = new spamtonprof\stp_api\AddLbcManager();

$nbHours = 2;
$cmd = "controlAccount";
if (! is_null($_GET["nb_hours"])) {
    $nbHours = trim($_GET["nb_hours"]);

}

if (! is_null($_GET["cmd"])) {
    $cmd = trim($_GET["cmd"]);
}


if ($cmd == "controlAccount") {

    $accounts = $lbcAccountMg->getAccountToCheck($nbHours);
    $msgs[] =  "Contrôle des nouveaux comptes après première publication";
    $msgs[] =  "Démarrage ...";
    
    if (! $accounts) {
        echo("deb");
        $msgs[] =  "Aucun nouveau compte à checker ";
        $msgs[] =  "fin du contrôle !";
        $slack ->sendMessages($slack::LogLbc, $msgs);
        return;
    }
    
    foreach ($accounts as $account) {
        
        $searchOperator = $account->getMail() . " mise en ligne";
        
        $messages = $gmailManager->listMessages($searchOperator);
        
        $nbMessages = count($messages);
        
        $msgs[] =  $account->getMail();
        $msgs[] =  "nb annonces validés : " ;
        $msgs[] =  count($messages);
        
        if ($nbMessages == 0) {
            
            $msgs[] =  "désactivation du compte faite";
            
            // virer les annonces de add_lbc
            $addLbcMg->delete(array(
                "ref_compte" => $account->getRef_compte()
            ));
            
            // mettre le compte comme disabled
            $account->setDisabled(true);
            $lbcAccountMg->updateDisabled($account);
        } else {
            $account->setDisabled(false);
        }
        
        $lbcAccountMg->updateDisabled($account);
        $msgs[] =  " ----- ";
    }
    
    
}
$msgs[] =  "fin du contrôle";

$slack -> sendMessages($slack::LogLbc, $msgs);
return;