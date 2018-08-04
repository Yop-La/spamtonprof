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
 * Version: 1.1.5.5.8
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

require_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/getresponse/GetResponseAPI3.class.php');

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

require_once (dirname(__FILE__) . '/ninjaFormHooks/afterSubmission.php');

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
        
        $stpProfilMg = new \spamtonprof\stp_api\stpProfilManager();
        
        $profils = $stpProfilMg->getAll();
        
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
        
        
        
        $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
        
        $profs = $stpProfMg->getAll();
        
        foreach ($profs as $prof) {
            
            $prof = $stpProfMg->cast($prof);
            
            $options[] = array(
                'label' => $prof ->getPrenom(). " " . $prof -> getNom(),
                'value' => $prof -> getRef_prof()
            );
        }
        
        wp_reset_postdata();
    }
    
    return $options;
}




