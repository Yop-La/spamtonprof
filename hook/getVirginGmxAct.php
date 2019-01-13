<?php
/**
 * 
 *  ppour générer un compte lbc avant publication d'annonces par zenno ( en prod )
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

if ($_POST['password'] = HOOK_SECRET) {

    $gmxActMg = new \spamtonprof\stp_api\GmxActManager();
    $gmxAct = $gmxActMg->get(array(
        'virgin'
    ));

    $ret = new \stdClass();
    $ret->gmx_act = $gmxAct;

    prettyPrint($ret);
}