<?php
/**
 * 
 *  pour mettre à jour la propriété smtp_enabled d'un compte gmx ( table gmx_act )
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

if ($_POST['password'] == HOOK_SECRET) {

    $ref_gmx_act = $_POST['ref_gmx_act'];

    $gmxActMg = new \spamtonprof\stp_api\GmxActManager();
    $gmxAct = $gmxActMg->get(array(
        'ref_gmx_act' => $ref_gmx_act
    ));

    $gmxActMg->delete($gmxAct);

    prettyPrint($gmxAct);
}