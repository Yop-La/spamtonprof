<?php

// toutes ces fonctions seront �x�cut�s par un appel ajax r�alis� sur n'importe quelle page

add_action('wp_ajax_ajaxLogOut', 'ajaxLogOut');

add_action('wp_ajax_nopriv_ajaxLogOut', 'ajaxLogOut');

/* pour d�connecter un utilisateur */
function ajaxLogOut()

{
    header('Content-type: application/json');
    
    wp_logout();
    
    echo (json_encode("ok"));
    
    die();
}
