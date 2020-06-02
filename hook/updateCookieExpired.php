<?php
/**
 * 
 *  pour mettre cookie expired à false d'un compte lbc ( prod sur zenno )
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
$password = $_GET['password'];

$slack = new \spamtonprof\slack\Slack();

if ($password == HOOK_SECRET) {

    $lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
    $lbcAccount = $lbcAccountMg->get(array(
        'ref_compte' => $ref_compte
    ));

    $lbcAccount->setCookie_expired(false);
    $lbcAccountMg->update_cookie_expired($lbcAccount);

    $slack->sendMessages('log-lbc', array(
        "Cookies de compte lbc n° " . $ref_compte . " à jour "
    ));
}