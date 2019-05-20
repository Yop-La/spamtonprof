<?php
bugbugbug
/*
 *
 * pour faire un contr�le des publications en ligne avec les mails envoy�s par leboncon
 *
 */
require_once (dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_reporting(- 1);
ini_set('display_errors', 'On');
set_error_handler("var_dump");

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


$titres = array(
    0 => array(
        'Stage maths été'
    ),
    1 => array(
        'Stage d\'été en maths par prof'
    ),
    2 => array(
        'Stage en ligne de maths d\'été'
    ),
    3 => array(
        'State en ligne individualisé de maths pour les vacances d\'été'
    ),
    4 => array(
        'Stage de renforcement et d\'approfondissement en maths'
    ),
    5 => array(
        'Stage de vacances maths'
    ),
    6 => array(
        'Préparez la rentrée avec un stage d\'été en maths'
    ),
    7 => array(
        'Mettez toutes les chances de votre côté avec le stage d\'été en maths'
    ),
    8 => array(
        'Stage méthodologique de préparation à l\'année supérieure en maths'
    ),
    9 => array(
        '1 semaine de stage pour réussir son année !'
    ),
    11 => array(
        'Stage maths de vacances d\'été'
    ),
    12 => array(
        'Stages en ligne illimités : 1 semaine pour tout changer en maths'
    ),
    13 => array(
        'Stage en ligne : webcam + téléphone + mails de 8h à 20h'
    ),
    14 => array(
        'Stage maths : 1 semaine pour tout changer en maths !'
    ),
    15 => array(
        'Stage en ligne de maths pour progresser vraiment'
    ),
    16 => array(
        'Stage maths avec partie méthodo pour enfin apprendre à réviser les maths'
    ),
    17 => array(
        'Stage de maths pour renforcer son niveau et apprendre à travailler en maths.'
    ),
    18 => array(
        'Sautez le pas avec le stage en ligne de maths !'
    ),
    19 => array(
        'Renforcer son niveau et enfin apprendre à travailler les maths avec un stage'
    ),
    20 => array(
        'Se préparer à l\'année supérieure en maths en 1 semaine !'
    )
);

$titles = [];

foreach ($titres as $titre) {
    
    $titre1 = $titre[0];
    $titre2 = 'Prof pour ' . $titre[0];
    if (strlen($titre1) >= 10 && strlen($titre1) <= 50) {
        $titles[] = $titre1;
    }
    if (strlen($titre2) >= 10 && strlen($titre2) <= 50) {
        $titles[] = $titre2;
    }
}
$typeTitreMg = new \spamtonprof\stp_api\TypeTitreManager();
$titleMg = new \spamtonprof\stp_api\LbcTitleManager();
$typeTitre = $typeTitreMg->get(array(
    'type' => 'stage_ete_stp'
));
if (! $typeTitre) {
    $typeTitre = $typeTitreMg->add(new \spamtonprof\stp_api\TypeTitre(array(
        'type' => 'stage_ete_stp'
    )));
}


foreach ($titles as $title){
    $titleMg->add(new \spamtonprof\stp_api\LbcTitle(array('titre' => $title,'type_titre'=>$typeTitre->getType(),'ref_type_titre'=>$typeTitre->getRef_type())));
    
}

prettyPrint(count($titles));
exit();