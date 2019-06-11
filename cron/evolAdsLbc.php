<?php
use spamtonprof\stp_api\GrCampaignMg;
use spamtonprof\stp_api\GrCustomFieldMg;
use spamtonprof\stp_api\GrTagMg;

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
 * pour traquer l'évolution du nombre d'annonces sur leboncoin
 * tourne tous les 5 minutes
 */

$lbcProcessMg = new \spamtonprof\stp_api\LbcApi();
$now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

$ads = $lbcProcessMg->get_maths_ads();

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('evolution-ads-lbc', array(
    $now->format(FR_DATETIME_FORMAT) . ":",
    $ads->total
));
