<?php
/*
 * pour contr�ler les annonces en ligne sur leboncoin ( en prod )
 *
 * tourne tous les jours pendant la nuit
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

// toujours avoir un cron sur cette tâche sinon le cookie expire et cela devient impossible de checker la page

// toujours mettre analyse_campagaigns pour éviter de republier sur des comptes bloqués avec des annonces en ligne

$lbc = new \spamtonprof\stp_api\LbcProcessManager();
$lbc->checkAds(5);
$lbc->analyse_campaigns();
$lbc->publish_campaigns_reporting();