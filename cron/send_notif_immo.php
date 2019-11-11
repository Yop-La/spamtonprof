<?php
/*
 * pour récupérer les annonces immos dans un rayon de 5km de Rennes
 *
 * tourne tous les heures
 *
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

define('PROBLEME_CLIENT', true);

$ads_immo_mg = new \spamtonprof\stp_api\LbcAdsImmoManager();
$smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
$smtp = $smtpMg->get(array(
    "ref_smtp_server" => $smtpMg::smtp2Go
));

$targets = [
    'Rennes',
    'Brest'
];


foreach ($targets as $target) {

    $ads = $ads_immo_mg->get_opportunities_coloc($target);
    $mail = file_get_contents(ABSPATH . 'wp-content/plugins/spamtonprof/emails/immo1.html');

    
    foreach ($ads as $ad) {
        $body = $mail;

        foreach ($ad as $key => $value) {
            $body = str_replace("[$key]", $value, $body);
        }

        $price = $ad["price"];

        $title = "[coloc à 3 et + à $target]- opportunité à " . $price . "€ ";
        
        
        $send = $smtp->sendEmail($title, 'alex.guillemine@gmail.com', $body, 'immo@lbc.fr', "Bot Immo Lbc", true);

        if ($send) {
            $ad = new \spamtonprof\stp_api\LbcAdsImmo($ad);
            $ad->setNotified(true);
            $ads_immo_mg->update_notified($ad);
        }
    }
}

exit();