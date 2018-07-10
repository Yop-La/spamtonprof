<?php
require_once (dirname(__FILE__) . "/wp-config.php");
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

$path = dirname(__FILE__) . "/wp-content/plugins/spamtonprof/stp_api";


$formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

$classeMg = new \spamtonprof\stp_api\stpClasseManager();

$matiereMg = new \spamtonprof\stp_api\stpMatiereManager();


$classe = $classeMg->get('pl');

$matiereMg = new \spamtonprof\stp_api\stpMatiereManager();
$matieres = [];
$mathsCoche = "1";
$physiqueCoche = "0";
$frenchCoche = "0";

if($frenchCoche == "1"){
    
    $matieres[] = $matiereMg -> get(array('matiere' => 'francais'));
    
}
if($mathsCoche == "1"){
    
    $matieres[] = $matiereMg -> get(array('matiere' => 'maths'));
    
}
if($physiqueCoche == "1"){
    
    $matieres[] = $matiereMg -> get(array('matiere' => 'physique'));
    
}

// étape n°8 : déterminer la formule

$formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

$formule = $formuleMg -> get(array('classe' => $classe, 'matieres' => $matieres));

$slack = new \spamtonprof\slack\Slack();

if(!$formule){
    
    $slack->sendMessages('log', array(
        'impossible de trouver la formule de cette classe : ' . $classe->getClasse()
    ));
    $error = 'formule-not-found';
    
}

prettyPrint($formule);

$planMg = new \spamtonprof\stp_api\StpPlanManager();



$plan = $planMg -> get(array('ref_formule' => $formule->getRef_formule(), 'nom' => 'defaut'));


