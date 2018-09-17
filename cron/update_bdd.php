<?php

/**
 *  sert à mettre à jour la bdd ( rien n'est fait pour les étudiants )
 *
 * ce script sert :
 *   - à calculer le nombre de jours d'inactivité
 *   - à compter les messages et mettre à jour le nb de message de la semaine dernière dans compte_eleve
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/wp-config.php");
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

----------------------  calculer le nombre de jours d'inactivitÃ© --------------------------

*/

$accountMg = new \spamtonprof\stp_api\AccountManager();

$accountMg->updateNbJourInactivite();

echo(' nb jour sans activité de compte_eleve maj'. "<br>");

/*
 *  
 *   ---- compter les messages de la semaine actuel 
 * 
 */

$now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

$week = $now -> format('W') ;

$year = $now -> format('Y');

$nbEmailManager = new \spamtonprof\stp_api\NbEmailManager();

$nbEmailManager->deleteList(array("week" => $week, "year" => $year));

$nbEmailManager->feed($week, $year);

echo(' table nb_email maj'. "<br>");

/*
 * 
 * 
 * mettre à jour le nb de message de la semaine dernière
 * 
 * 
 */

$accountMg -> resetNbMessageLastWeek();

if($week == 1){
    $week = 52;
    $year = $year - 1;
}else{
    $week = $week-1;
}

$accountMg -> updateNbMessageLastWeek($week, $year);

echo(' nb message last week de compte_eleve maj'. "<br>");