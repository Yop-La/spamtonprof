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


$now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
$before = clone $now;
$before->sub(new \DateInterval('PT5M'));

$nb_new_ads = 0;
$total = 0;
$continue = true;
$offset = 0;
do {
    
    $lbcProcessMg = new \spamtonprof\stp_api\LbcApi();
    $ads = $lbcProcessMg->get_maths_ads($offset);
    $total = $ads->total;
    $ads = $ads->ads;
    
    foreach ($ads as $ad) {
        
        $first_publication_date = $ad->index_date;
        $first_publication_date = DateTime::createFromFormat(LBC_DATETIME_FORMAT, $first_publication_date, new \DateTimeZone("Europe/Paris"));
        
        echo ($before->format(FR_DATETIME_FORMAT) . '<br>');
        
        echo ($now->format(FR_DATETIME_FORMAT) . '<br>'. '<br>'. '<br>');
        
        
        echo ($first_publication_date->format(FR_DATETIME_FORMAT) . '<br>');
        
        
        if ($first_publication_date >= $before && $first_publication_date <= $now) {
            $nb_new_ads = $nb_new_ads + 1;
            echo('dedans <br>');
        } else {
            $continue = false;
            break;
        }
    }
    $offset = $offset + 35;
} while ($continue);

$slack = new \spamtonprof\slack\Slack();
$slack->sendMessages('evolution-ads-lbc', array(
    $now->format(FR_DATETIME_FORMAT) . ":",
    'total: ' . $total,
    'Nb nouvelles annonces des 5 dernières minutes: ' . $nb_new_ads
));
