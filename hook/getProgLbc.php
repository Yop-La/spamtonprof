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

/*
 *
 * est appele par le template zenno afin de recuperer le compte a publier
 * et de le notifier dans slack afin d'estimer le temps de publication
 *
 */

$slack = new \spamtonprof\slack\Slack();

$index = $_GET["index"];

$ggMg = new \spamtonprof\googleMg\GoogleManager('soutien.par.mail@gmail.com');

$rows = $ggMg->readSheet();

$nbRows = count($rows);

if ($index >= $nbRows) {
    prettyPrint('false');

    $slack->sendMessages('log-lbc', array(
        "Fin de publication : le sheet a ete parcouru en entier"
    ));
}

$clientMg = new \spamtonprof\stp_api\LbcClientManager();


$slack->sendMessages('log-lbc', array(
    "   --------------   ",
    "LBC : publication de la ligne du sheet n : " . ($index + 1) . " sur " . $nbRows,
    implode(" - ", $rows[$index])
));

$ret = new \stdClass();
$ret->prog = $rows[$index];

prettyPrint($ret);

