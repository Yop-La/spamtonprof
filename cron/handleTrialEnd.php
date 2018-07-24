<?php
use spamtonprof\slack\Slack;
use spamtonprof\gmailManager\GmailManager;

/**
 * pour la boite mailsfromlbc@gmail.com - adaption possible sur d'autres boites
 *
 *
 * ce script sert :
 * - à enregistrer les messages de prospects dans la bdd
 * - à attribuer des libellées aux emails
 *
 *
 * il tourne tous les 5 minutes
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

$accountMg = new \spamtonprof\stp_api\AccountManager();

$refComptes = $accountMg->getTrialEndAccounts();

$accounts = $accountMg->getAll($refComptes);

foreach ($accounts as $account) {
    
    $nbMessage = $accountMg -> getNbMessage($account->ref_compte());
    
    $slack->sendMessages("trial-end-account", array_merge(array(" 7 jours d'essai pour ce compte : " . $account->ref_compte(),
        " -- eleve -- ",
        $account->eleve()->prenom(),
        $account->eleve()->nom(),
        $account->eleve()->getTelephone(),
        $account->eleve()->adresse_mail(),
        " -- parent -- ",
        $account->proche()->prenom(),
        $account->proche()->nom(),
        $account->proche()->getTelephone(),
        $account->proche()->adresse_mail(),
        " -- matières & activités  --"),
        $account->getMatieres(),
        array("nb messages : " .  $nbMessage, "           -                  "))
        
    );

}
