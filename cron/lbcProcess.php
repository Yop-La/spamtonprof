<?php
use spamtonprof\slack\Slack;
use spamtonprof\gmailManager\GmailManager;

/**
 * pour la boite mailsfromlbc@gmail.com - adaption possible sur d'autres boites
 *
 *
 * ce script sert :
 * - � enregistrer les messages de prospects dans la bdd
 * - � attribuer des libell�es aux emails
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


$date = new \DateTime(null);

$minute = (int) $date->format('i');

$lbcProcessMg = new \spamtonprof\stp_api\LbcProcessManager();

echo("minute : " .$minute  . "<br>");

if($minute % 2 == 0){
    echo("process 1 : lecture des emails de mailsfromlbc@gmail.com" . "<br>");
    $lbcProcessMg -> readNewLeadMessages();
    
}else{
    echo("process 2 : redirection des emails vers lebureaudesprofs + envoi des emails aux prospects depuis mailsfromlbc@gmail.com"  . "<br>");
    $lbcProcessMg ->processNewMessages(); 
}
