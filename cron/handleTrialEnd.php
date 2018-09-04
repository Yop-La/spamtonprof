<?php
use spamtonprof\slack\Slack;
use spamtonprof\gmailManager\GmailManager;

/**
 * pour la boite mailsfromlbc@gmail.com - adaption possible sur d'autres boites
 *
 *
 * ce script sert :
 * - à enregistrer les messages de prospects dans la bdd
 * - à attribuer des libellées aux emails
 *
 *
 * il tourne tous les 5 minutes
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

$abos = $aboMg->getTrialCompleted();

foreach ($abos as $abo) {
    
    $nbMessage = 0;
    
    $eleve = $abo->getEleve();
    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
    
    $prof = $abo->getProf();
    $prof = \spamtonprof\stp_api\StpProf::cast($prof);
    
    $parent = $abo->getProche();
    if ($parent) {
        $parent = \spamtonprof\stp_api\StpProche::cast($parent);
    }
    
    $formule = $abo -> getFormule();
    $formule = \spamtonprof\stp_api\StpFormule::cast($formule);
    
    $msgs = array(
        " 7 jours d'essai pour cet abonnement : " . $abo->getRef_abonnement(),
        " -- eleve -- ",
        $eleve->getPrenom(),
        $eleve->getNom(),
        $eleve->getTelephone(),
        $eleve->getEmail()
    );
    
    if ($parent) {
        
        $msgs = array_merge($msgs, array(
            " -- parent -- ",
            $parent->getPrenom(),
            $parent->getNom(),
            $parent->getTelephone(),
            $parent->getEmail()
        ));
    }
    
    $msgs = array_merge($msgs, array(
        " -- matières & activités  & prof --",
        $formule->getFormule(),
        $prof->getPrenom() . " " . $prof->getNom(),
        "nb messages : " . $nbMessage,
        "           -                  "
    ));
    
    $slack->sendMessages("trial-end-account", $msgs);
}
