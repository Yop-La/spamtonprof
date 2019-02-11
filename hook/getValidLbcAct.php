<?php
/**
 * 
 *  renvoie un compte leboncoin valid� (ie avec des annonces) ayant une adresse mail
 *  d'un nom de domaine appartenant � spamtonprof
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

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log-lbc', array(
    'publication sur un compte existant'
));

$password = $_POST['password'];
$ref_client = $_POST['ref_client'];
$act_type = $_POST['act_type'];

if ($password == HOOK_SECRET) {

    $lbcActMg = new \spamtonprof\stp_api\LbcAccountManager();
    $act = $lbcActMg->get(array(
        'valid_lbc_act' => $ref_client,
        'act_type' => $act_type
    ));

    $ret = new \stdClass();
    $ret->lbc_act = $act;


    prettyPrint($ret);
} else {
    prettyPrint(false);
}

