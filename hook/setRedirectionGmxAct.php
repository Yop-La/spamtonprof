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

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('log', array_values($_POST));

if ($_POST['password'] = HOOK_SECRET) {

    $ref_gmx_act = $_POST['ref_gmx_act'];
    $has_redirection = $_POST['has_redirection'];

    if ($has_redirection == "true") {
        $has_redirection = true;
    } else {
        $has_redirection = false;
    }

    $gmxActMg = new \spamtonprof\stp_api\GmxActManager();
    $gmxAct = $gmxActMg->get(array(
        'ref_gmx_act' => $ref_gmx_act
    ));

    $gmxAct->setHas_redirection($has_redirection);
    $gmxActMg->updateHasRedirection($gmxAct);

    prettyPrint($gmxAct);
}