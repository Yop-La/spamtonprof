<?php
/**
 * 
 *  ppour g�n�rer un compte lbc avant publication d'annonces par zenno ( en prod )
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

$ref_url = $_GET['ref_url'];
$status = $_GET['status'];

$lbcRenewalUrlMg = new \spamtonprof\stp_api\LbcRenewalUrlManager();
$url = $lbcRenewalUrlMg->get(array(
    'ref_url' => $ref_url
));

$url->setStatut($status);

$lbcRenewalUrlMg->updateStatut($url);

prettyPrint($url);