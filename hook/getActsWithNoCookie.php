<?php
/**
 * 
 *  pour récupérer les comptes leboncoin qui n'ont pas de cookies( en prod )
 *  Le cookies a expiré à cause d'un contrôle d'annonces trop tardives
 *  Pour savoir quels comptes, on contrôle les comptes pour lesquels on a reçu un email de " nouvelle annonce en ligne "
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

$password = $_GET['password'];

if ($password == HOOK_SECRET) {
    $lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();

    $acts = $lbcAccountMg->getAll(array(
        'expired_cookie_with_ads' => null
    ));

    $nb_acts = count($acts);

    $ret = new \stdClass();
    $ret->acts = $acts;
    $ret->nb_acts = $nb_acts;

    $slack->sendMessages('log-lbc', array(
        "Récupération de $nb_acts comptes sans cookies"
    ));

    prettyPrint($ret);
}
    


