<?php

// toutes ces fonctions seront éxécutés par un appel ajax réalisé soit au moment de la connexion ou au moment de la déconnexion
add_action('wp_ajax_ajaxLogOut', 'ajaxLogOut');

add_action('wp_ajax_nopriv_ajaxLogOut', 'ajaxLogOut');

add_action('wp_ajax_ajaxCheckLogIn', 'ajaxCheckLogIn');

add_action('wp_ajax_nopriv_ajaxCheckLogIn', 'ajaxCheckLogIn');

/* pour déconnecter un utilisateur */
function ajaxLogOut()

{
    header('Content-type: application/json');
    
    wp_logout();
    
    echo (json_encode("ok"));
    
    die();
}

/* vérifier username et password au moment du log in */
function ajaxCheckLogIn()

{
    
    $retour = "ok";
    $user;
    
    header('Content-type: application/json');
    
    $canLog = false;
   
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $user = get_user_by('email', $username);
    
    if ($user && wp_check_password($password, $user->data->user_pass, $user->ID)) {
        $canLog = true;
    }
    
    if ($canLog) {
        
        $user = wp_signon(array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        ));
    }
    
    if($canLog){
        $retour = $user;
    }else{
        $retour = false;
    }
    
    echo (json_encode($retour));
    
    die();
}