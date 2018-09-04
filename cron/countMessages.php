<?php

/**
 * ce script sert :
 * - à compter les messages envoyés par les élèves durant les 7 derniers jours
 *
 * fonctionnement :
 * 1°) compter les messages des 7 derniers jours dans stp_message_eleve
 * 2°) mettre à jour la colonne nb_message dans stp_message_eleve
 *
 * il tourne tous les 2 heures - en prod
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

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$nbMessages = $aboMg->getNbMessage();

$aboMg->resetNbMessage();

foreach ($nbMessages as $nbMessage) {
    
    $abo = $aboMg->get(array(
        "ref_abonnement" => $nbMessage["ref_abonnement"]
    ));
    
    $abo->setNb_message($nbMessage["nb_message"]);
    $aboMg->updateNbMessage($abo);
    
}

