<?php
/**
 * 
 *  pour generer un compte lbc avant publication d'annonces par zenno ( en prod )
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
 * - le temps d'attente avant nouvelle tentative de recuperation
 * - le nombre de tentatives a faire
 */

$gmail = new \spamtonprof\googleMg\GoogleManager('mailsfromlbc@gmail.com');
$slack = new \spamtonprof\slack\Slack();

$email = $_POST['email'];
$timeBreak = $_POST['timeBreak'];
$nbTry = $_POST['nbTry'];

$ret = new \stdClass();

$indexTry = 0;

do {
    $msgs = $gmail->listMessages($email . ' "Confirmez la demande"');
    
    if (count($msgs) == 1) {
        
        $msg = $msgs[0];
        
        $msg = $gmail->getMessage($msg->id, [
            'format' => 'full'
        ]);
        
        $body = $gmail->getBody($msg);
        
        $matches = array();
        
        $pattern = '#\bhttps?://.*leboncoin.fr/compte/part/activation[^"]*#';
        preg_match_all($pattern, $body, $matches);
        
        $confirmationUrl = $matches[0][0];
        
        $ret->url = $confirmationUrl;
        prettyPrint($ret);
    }
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
prettyPrint($ret);