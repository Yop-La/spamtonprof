<?php
/**
 * 
 *  pour récupérer le code de validation au moment du login leboncoin( en prod )
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

// pour recuperer l'url de confirmation de creation d'un compte lbc
/*
 * parametres :
 * - l'email du compte lbc
 */

$gmail = new \spamtonprof\googleMg\GoogleManager('mailsfromlbc@gmail.com');
$slack = new \spamtonprof\slack\Slack();

$email = $_POST['email'];
$timeBreak = 30;
$nbTry = 5;

$ret = new \stdClass();

$indexTry = 0;

do {
    $msgs = $gmail->listMessages($email . " veuillez entrer le code suivant");

    $msg = $msgs[0];

    $msg = $gmail->getMessage($msg->id, [
        'format' => 'full'
    ]);

    $body = $gmail->getBody($msg);

    $doc = new DOMDocument();
    $doc->loadHTML($body);
    $liList = $doc->getElementsByTagName('li');
    $liValues = array();
    foreach ($liList as $li) {
        $liValues[] = trim($li->nodeValue);
    }

    $code = implode("", $liValues);

    sleep($timeBreak);
} while ($nbTry != $indexTry);

exit();

$slack->sendMessages('log-lbc', array(
    'Code de login récupéré : ' . $code
));

$ret->code = $code;
prettyPrint($ret);