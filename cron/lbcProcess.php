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

$lbcProcessMg = new \spamtonprof\stp_api\LbcProcessManager();

$lbcReaderint = unserializeTemp("/tempo/lbcReaderInt");

if (! $lbcReaderint) {  
    $lbcReaderInt = - 1;
    serializeTemp($lbcReaderInt, "/tempo/lbcReaderInt");
}

if ($lbcReaderint == - 1) {
    $lbcReaderInt = 1;
} elseif ($lbcReaderint == 1) {
    $lbcReaderInt = - 1;
}

serializeTemp($lbcReaderInt, "/tempo/lbcReaderInt");

if ($lbcReaderint == - 1) {
    echo ("process 1 : lecture des emails de mailsfromlbc@gmail.com" . "<br>");
    $lbcProcessMg->readNewLeadMessages();
} elseif ($lbcReaderint == 1) {
    echo ("process 2 : redirection des emails vers lebureaudesprofs + envoi des emails aux prospects depuis mailsfromlbc@gmail.com" . "<br>");
    $lbcProcessMg->processNewMessages();
}