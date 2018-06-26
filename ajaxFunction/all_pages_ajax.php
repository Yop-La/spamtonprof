<?php

// toutes ces fonctions seront éxécutés par un appel ajax réalisé sur n'importe quelle page

add_action('wp_ajax_ajaxLogOut', 'ajaxLogOut');

add_action('wp_ajax_nopriv_ajaxLogOut', 'ajaxLogOut');

/* pour déconnecter un utilisateur */
function ajaxLogOut()

{
    header('Content-type: application/json');
    
    wp_logout();
    
    echo (json_encode("ok"));
    
    die();
}
