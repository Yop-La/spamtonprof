<?php

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

// en prod. Toutes les heures
/*
 * cron de détermination des abos à relancer
 */



$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$aboMg->updateAll(array(
    "key" => "trial_sub_not_relaunched_to_relaunch",
    "days_since_last_contact" => 3
));

$aboMg->updateAll(array(
    "key" => "trial_sub_relaunched_to_relaunch",
    "days_since_last_relaunch" => 3
));

$aboMg->updateAll(array(
    "key" => "actif_sub_not_relaunched_to_relaunch",
    "days_since_last_contact" => 5
));

$aboMg->updateAll(array(
    "key" => "actif_sub_relaunched_to_relaunch",
    "days_since_last_relaunch" => 3
));
