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
if (array_key_exists('automatic_answer', $_GET) && $_GET['automatic_answer'] == "false") {

    $automatic_answer = false;

}

$lbcProcessMg = new \spamtonprof\stp_api\LbcProcessManager();
$lbcProcessMg->read_messages_mailfromlbc();
$lbcProcessMg->process_new_lead_messages();
$lbcProcessMg->send_reply_to_lead();

$lbcProcessMg2 = new \spamtonprof\stp_api\LbcProcessManager("le.bureau.des.profs@gmail.com");
$lbcProcessMg2->read_messages_lebureaudesprofs();
$lbcProcessMg2->label_forwarded_messages();
