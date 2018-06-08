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

/*
 * ce script va tourner tous les 5 minutes pour lire les nouveaux mails
 * de l'adresse mailsfromlbc@gmail.com. Il sert à lire les mails envoyés par les prospects depuis leboncoin
 * et à les enregistrer dans une table afin de leur répondre par la suite avec un autre script
 *
 */

$lbcProcessManager = new \spamtonprof\stp_api\LbcProcessManager();

$lbcProcessManager->processNewLeadMessages();

sleep(10); // pour avoir une chronologie dans les messages sur slack

echo("passsssssseeeeeee");

$lbcProcessManager->answerToLeadMessages();
