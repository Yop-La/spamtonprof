<?php
/**
 * 
 *  pour g�n�rer des annonces lbc avant publication par zenno
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

/* cr�e le 21 oct 2018 - en prod
 * Ce script sert � :
 *  - premi�r partie : mettre en pause un abonnement ( sur stripe , dans la base stp et dans algolia) 
 *  - deux�ime partie : mettre fin � la pause d'un abonnement ( dans stp et aloglia )
 *  
 *  
 */


$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
$algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
$stripe = new \spamtonprof\stp_api\StripeManager(false);

$stpInterruptionMg = new \spamtonprof\stp_api\StpInterruptionManager();

/// premi�re partie - mettre les abonnements en interruptions
$now = new \DateTime(null,new \DateTimeZone('Europe/Paris'));


// on r�cup�re toutes les interruptions qui commencent
$interruptions = $stpInterruptionMg -> getAll(array('debut' => $now->format(PG_DATE_FORMAT)));



foreach ($interruptions as $interruption){
    
    $refAbo = $interruption -> getRef_abonnement();
    
    $abo = $aboMg -> toAlgoliaSupport($interruption -> getRef_abonnement());
    
    // mise � jour de la table stp_abonnement ( mise � jour boolean "interruption")
    $abo -> setInterruption(true);
    $aboMg -> updateInterruption($abo);
    
    // mise � jour de de l'index support client dans algolia     
    $algoliaMg -> updateSupport($abo);

    // ajout d'une p�riode d'essai � stripe
    $prorate = false;
    
    $stripe -> addTrial($abo->getSubs_Id(),$interruption->getFin(), $prorate);
    
    
    // envoyer les emails � faire ...
    
    
}


// pour s'occuper des prolongations
$interruptions = $stpInterruptionMg -> getAll(array('interruption' => $now->format(PG_DATE_FORMAT)));

foreach ($interruptions as $interruption){
    
    $refAbo = $interruption -> getRef_abonnement();
    
    $abo = $aboMg -> get(array("ref_abonnement" => $refAbo));
    
    $stripe -> addTrial($abo->getSubs_Id(),$interruption->getFin(), $prorate);
    
    // mise � jour de la table stp_abonnement ( mise � jour boolean "interruption")
    $abo -> setInterruption(true);
    $aboMg -> updateInterruption($abo);
    
    // mise � jour de de l'index support clietn dans algolia
    $algoliaMg -> updateAbo($abo);
    
    //envoyer les emails � faire ...
    
    
}




// on r�cup�re toutes les interruptions qui se terminent
$interruptions = $stpInterruptionMg -> getAll(array('fin' => $now->format(PG_DATE_FORMAT)));

foreach ($interruptions as $interruption){
    
    $refAbo = $interruption -> getRef_abonnement();
    
    $abo = $aboMg -> get(array("ref_abonnement" => $refAbo));
    
    // mise � jour de la table stp_abonnement ( mise � jour boolean "interruption")
    $abo -> setInterruption(true);
    $aboMg -> updateInterruption($abo);
    
    // mise � jour de de l'index support clietn dans algolia
    $algoliaMg -> updateAbo($abo);
    
    //envoyer les emails � faire ...
    
    
}
    
