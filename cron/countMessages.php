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

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

//récupérer les messages et les comptes des abonnements avec nb_messages != 0
$nbMessages = $aboMg->getNbMessage();

// mettre tous les messages à zéro
$aboMg->resetNbMessage();

//reset de l'index
$algolia = new \spamtonprof\stp_api\AlgoliaManager();
$algolia -> resetNbMessage();

// mettre à jour les messages non nulles dans algolia : 
$refAbos = [];
foreach ($nbMessages as $nbMessage) {
    
    $abo = $aboMg->get(array(
        "ref_abonnement" => $nbMessage["ref_abonnement"]
    ));
    
    $abo->setNb_message($nbMessage["nb_message"]);
    $aboMg->updateNbMessage($abo);

    $refAbos[] = $abo->getRef_abonnement();
    
}

$algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
$algoliaMg->updateAbonnements($refAbos, false);


