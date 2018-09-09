<?php

/*
 *
 *
 *
 * Plugin Name: spamtonprof
 *
 *
 *
 * Plugin URI: http://spamtonprof.com
 *
 *
 *
 * GitHub Plugin URI: https://github.com/Yop-La/spamtonprof
 *
 *
 *
 * Description: Un plugin pour intégrer l'api de spamtonprof
 *
 *
 *
 * Version: 1.1.5.8.7
 *
 *
 *
 * Author: yopla
 *
 *
 *
 * Author URI: http://spamtonprof.com
 *
 *
 *
 * License: GPL2
 *
 *
 *
 */
require_once (dirname(__FILE__) . '/inc/autoloader_stp.php'); // autoloader des classes stp

require_once (dirname(__FILE__) . '/inc/autoloader_getresponse.php'); // autoloader les custom classes de getresponse

require_once (dirname(__FILE__) . '/inc/autoloader_gmail.php'); // autoloader les custom classes de getresponse

require_once (dirname(__FILE__) . '/inc/autoloader_cnl.php'); // autoloader les custom classes de cnl

require_once (dirname(__FILE__) . '/inc/autoloader_lbc.php'); // autoloader les custom classes de lbc

require_once (dirname(__FILE__) . '/vendor/autoload.php'); // autoload strip, paypal , gmail

require_once (dirname(__FILE__) . '/vendor/getresponse/GetResponseAPI3.class.php');

require_once (dirname(__FILE__) . '/slack/Slack.php'); // pour communiquer avec slack

require_once (dirname(__FILE__) . '/dev-tools.php');

/* require tous les fichiers contenant des fonctions ajax */

require_once (dirname(__FILE__) . '/ajaxFunction/page-paiement-ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/page-inscription-essai_eleve-ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/adds_back_office_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/discover_week_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/log_in_and_out.php');

require_once (dirname(__FILE__) . '/ajaxFunction/inscription_prof_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/onboarding_prof_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/choisir-prof.php');

require_once (dirname(__FILE__) . '/ajaxFunction/ajax_dashboard_eleve.php');

require_once (dirname(__FILE__) . '/ninjaFormHooks/afterSubmission.php');

add_action('template_redirect', 'handleRedirections');

function handleRedirections()
{

    $current_user = wp_get_current_user();
    
    
    if ($current_user->ID != 0) {
        
        if (current_user_can('prof') && is_user_logged_in()) {
            
            
            
            $profMg = new \spamtonprof\stp_api\StpProfManager();
            
            $prof = $profMg->get(array(
                'user_id_wp' => $current_user->ID
            ));
            
            if (! $prof->getOnboarding() && ! is_page('onboarding-prof')) {
                
                $_SESSION['message'] = utf8_encode("Terminez l 'inscription pour donner des cours ! ");
                
                
                if (wp_redirect(home_url('onboarding-prof'))) {
                    
                    
                    
                    exit();
                }
            }
            
            if ($prof->getOnboarding() && is_page('inscription-prof')) {
                $_SESSION['message'] = utf8_encode("Pas besoin de faire l'inscription deux fois  :) ");

                if (wp_redirect(home_url('dashboard-prof'))) {
                
                    exit();
                }
            }
            
            if ($prof->getOnboarding() && is_page('onboarding-prof')) {

                if (wp_redirect(home_url('dashboard-prof'))) {
                    $_SESSION['message'] = utf8_encode("Pas besoin de faire l'inscription deux fois  :) ");

                    exit();
                }
            }
        }
        
        if (current_user_can('client') && is_user_logged_in()) {
            
            if (is_page('accueil')) {
                
                $_SESSION['message'] = utf8_encode("Bienvenue sur SpamTonProf !");
                if (wp_redirect(home_url('dashboard-eleve'))) {
                    exit();
                }
            }
        }
    }
}

add_action('init', 'stp_session_start', 1);

function stp_session_start()
{
    if (! session_id()) {
        
        session_start();
    }
}

add_action('wp_enqueue_scripts', 'handleScriptAndTestModeOnPage');

function handleScriptAndTestModeOnPage()
{
    global $post;
    $pageSlug = $post->post_name;
    $PageManager = new \spamtonprof\stp_api\PageManager($pageSlug);
}

add_filter('ninja_forms_render_options', 'my_pre_population_callback', 10, 2);

function my_pre_population_callback($options, $settings)
{
    
    // target profil field in discover week form
    if ($settings['key'] == 'profil_1532954478855') {
        
        $profils = [];
        
        $StpProfilMg = new \spamtonprof\stp_api\StpProfilManager();
        
        $profils = $StpProfilMg->getAll();
        
        foreach ($profils as $profil) {
            
            $options[] = array(
                'label' => $profil->getProfil(),
                'value' => $profil->getRef_profil()
            );
        }
        
        wp_reset_postdata();
    }
    
    // target "choisir prof" field in choisir_prof
    if ($settings['key'] == 'choisir_le_prof_1533217231976') {
        
        $StpProfMg = new \spamtonprof\stp_api\StpProfManager();
        
        $profs = $StpProfMg->getAll();
        
        foreach ($profs as $prof) {
            
            $prof = $StpProfMg->cast($prof);
            
            $options[] = array(
                'label' => $prof->getPrenom() . " " . $prof->getNom(),
                'value' => $prof->getRef_prof()
            );
        }
        
        wp_reset_postdata();
    }
    
    return $options;
}




