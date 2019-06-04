<?php
/**
 * 
 *  ppour generer un compte lbc avant publication d'annonces par zenno ( en prod )
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

$ref_compte = $_GET['ref_compte'];

$lbcRenewalUrlMg = new \spamtonprof\stp_api\LbcRenewalUrlManager();
$urls = $lbcRenewalUrlMg -> getAll(array('to_renew' => $ref_compte));

$retour = "false";
$lbcAccountManager = new \spamtonprof\stp_api\LbcAccountManager();


if(count($urls) != 0){
    $act = $lbcAccountManager ->get(array('ref_compte' => $urls[0] ->getRef_compte_lbc()));
    $retour = array('urls' => $urls, 'act' => $act);
}


prettyPrint(array('retour' => $retour));