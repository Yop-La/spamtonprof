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

$msgs[] =  "d�but du contr�le";



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
    $msgs[] =  "Contr�le des nouveaux comptes apr�s premi�re publication";
    $msgs[] =  "D�marrage ...";
    
    if (! $accounts) {
        echo("deb");
        $msgs[] =  "Aucun nouveau compte � checker ";
        $msgs[] =  "fin du contr�le !";
        $slack ->sendMessages($slack::LogLbc, $msgs);
        return;
    }
    
    foreach ($accounts as $account) {
        
        $searchOperator = $account->getMail() . " mise en ligne";
        
        $messages = $gmailManager->listMessages($searchOperator);
        
        $nbMessages = count($messages);
        
        $msgs[] =  $account->getMail();
        $msgs[] =  "nb annonces valid�s : " ;
        $msgs[] =  count($messages);
        
        if ($nbMessages == 0) {
            
            $msgs[] =  "d�sactivation du compte faite";
            
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
$msgs[] =  "fin du contr�le";

$slack -> sendMessages($slack::LogLbc, $msgs);
return;