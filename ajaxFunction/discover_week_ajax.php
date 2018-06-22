<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans discover_week.js sur la page dont le slug est semaine-decouverte

add_action('wp_ajax_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

add_action('wp_ajax_nopriv_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');



/* pour gérer la soumission du formulaire d'essai */
function ajaxAfterSubmissionEssai()

{
    header('Content-type: application/json');

    /* récupération des variables */
    $prenomEleve = $_POST["prenomEleve"];
    $nomEleve = $_POST["nomEleve"];
    $emailEleve = $_POST["emailEleve"];
    $phoneEleve = $_POST["phoneEleve"];
    $profil = $_POST["profil"];
    $classe = $_POST["classe"];
    $chapterMaths = $_POST["chapterMaths"];
    $lacuneMaths = $_POST["lacuneMaths"];
    $noteMaths = $_POST["noteMaths"];
    $chapterPhysique = $_POST["chapterPhysique"];
    $lacunePhysique = $_POST["lacunePhysique"];
    $notePhysique = $_POST["notePhysique"];
    $chapterFrench = $_POST["chapterFrench"];
    $lacuneFrench = $_POST["lacuneFrench"];
    $noteFrench = $_POST["noteFrench"];
    $proche = $_POST["proche"];
    $prenomProche = $_POST["prenomProche"];
    $nomProche = $_POST["nomProche"];
    $mailProche = $_POST["mailProche"];
    $phoneProche = $_POST["phoneProche"];
    $remarque = $_POST["remarque"];
    $code = $_POST["code"];
    $mathsCoche = $_POST["mathsCoche"];
    $physiqueCoche = $_POST["physiqueCoche"];
    $frenchCoche = $_POST["frenchCoche"];
    
    $slack = new \spamtonprof\slack\Slack();
    
    $slack -> sendMessages('log', array($prenomEleve));
    
    
    echo (json_encode("ok"));
    
    die();
}
