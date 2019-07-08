<?php
/**
 * 
 *  pour récupérer le lien permettant de valider le transfert des emails du compte gmx vers mailsfromlbc@gmail.com
 *  
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

$gmail = new \spamtonprof\googleMg\GoogleManager('mailsfromlbc@gmail.com');
$slack = new \spamtonprof\slack\Slack();

$timeBreak = $_POST['timeBreak'];
$nbTry = $_POST['nbTry'];

$gmail_adress = str_replace(" ", "+", trim($_POST['gmail']));

$ret = new \stdClass();

$indexTry = 0;

do {

    $slack->sendMessages('log-lbc', array(
        'Email de redirection : ' . $gmail_adress
    ));

    $msgs = $gmail->listMessages('"Confirm e-mail forwarding to your inbox" ' . $gmail_adress);

    $msg = $msgs[0];

    $msg = $gmail->getMessage($msg->id, [
        'format' => 'full'
    ]);

    $body = $gmail->getBody($msg);

    $matches = array();

    $pattern = '#\bhttps://forwarding.gmx.com/.*#';
    preg_match_all($pattern, $body, $matches);

    $confirmationUrl = $matches[0][0];

    // $confirmationUrl = str_replace('confirm', 'confirm/success', $confirmationUrl);

    $ret->url = $confirmationUrl;
    prettyPrint($ret);

    $indexTry = $indexTry + 1;
    $slack->sendMessages('log-lbc', array(
        'Echec' . $indexTry . ' de recuperation du mail de confirmation pour l\'email : ' . $email
    ));
    sleep($timeBreak);
} while ($nbTry != $indexTry);

$slack->sendMessages('log-lbc', array(
    'Impossible de recuperer l\'email de confirmation pour l\'email : ' . $email . '. Fin de publication.'
));

$ret->url = "no_confirmation_email_found";
