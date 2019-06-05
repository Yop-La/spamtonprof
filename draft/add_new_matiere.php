<?php

/*
 *
 * pour ajouter des nouvelles matières
 * 
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

// on ajoute d'abord les formules et leurs plans ( etape 1 ) - puis etape 2 - puis etape 3



$matieres = [
    'histoire',
    'geographie'
];
$matieres_complet = [
    'Histoire',
    'Géographie'
];

// ajout des matières à la table
$matiereMg = new \spamtonprof\stp_api\StpMatiereManager();

for ($i = 0; $i < count($matieres); $i ++) {
    
    $matiere = $matiereMg->get(array(
        'matiere' => $matieres[$i]
    ));
    
    if (! $matiere) {
        $matiere = $matiereMg->add(new \spamtonprof\stp_api\StpMatiere(array(
            'matiere' => $matieres[$i],
            'matiere_complet' => $matieres_complet[$i]
        )));
    }
}

// ajout des matières à getresponse

if (defined('PROBLEME_CLIENT')) {
    $matiereMg->resetGrTags();
}


$algolia = new \spamtonprof\stp_api\AlgoliaManager();
$algolia ->resetMatiereIndex();


