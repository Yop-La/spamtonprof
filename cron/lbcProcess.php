<?php
use spamtonprof\slack\Slack;

/**
 * pour la boite mailsfromlbc@gmail.com - adaption possible sur d'autres boites
 *
 *
 * ce script sert :
 * - � enregistrer les messages de prospects dans la bdd
 * - � attribuer des libell�es aux emails
 *
 *
 * il tourne tous les 5 minutes et il est en prod
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

$automatic_answer = true;
$nb_step = 3;
if (array_key_exists('automatic_answer', $_GET) && $_GET['automatic_answer'] == "false") {

    $automatic_answer = false;
    $nb_step = 2;
}

$lbcReaderInt = unserializeTemp("/tempo/lbcReaderInt");

if (! $lbcReaderInt) {
    $lbcReaderInt = 0;
    serializeTemp($lbcReaderInt, "/tempo/lbcReaderInt");
}

if ($lbcReaderInt == 0) {
    echo ("process 1 : lecture des emails de mailsfromlbc@gmail.com" . "<br>");
    $lbcProcessMg->readNewLeadMessages();
} elseif ($lbcReaderInt == 1) {
    echo ("process 2 : redirection des emails vers lebureaudesprofs + envoi des emails aux prospects depuis mailsfromlbc@gmail.com" . "<br>");
    $lbcProcessMg->processNewMessages();
} elseif ($automatic_answer && $lbcReaderInt == 2) {
    echo ("process 3 : réponse automatique aux premiers messages des prospects" . "<br>");
    $lbcProcessMg->sendAutomaticAnswer();
}

$lbcReaderInt = $lbcReaderInt + 1;
$lbcReaderInt = $lbcReaderInt % $nb_step;

serializeTemp($lbcReaderInt, "/tempo/lbcReaderInt");
