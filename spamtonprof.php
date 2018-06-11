<?php



/*

 *

 * Plugin Name: spamtonprof

 *

 * Plugin URI: http://spamtonprof.com

 *

 * GitHub Plugin URI: https://github.com/Yop-La/spamtonprof

 *

 * Description: Un plugin pour intÃ©grer l'api de spamtonprof

 *

 * Version: 1.1.5.3.2

 *

 * Author: yopla

 *

 * Author URI: http://spamtonprof.com

 *

 * License: GPL2

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

require_once (dirname(__FILE__) . '/ajaxFunction/page-paiement-ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/page-inscription-essai_eleve-ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/adds_back_office_ajax.php');

require_once (dirname(__FILE__) . '/ninjaFormHooks/afterSubmission.php');

require_once (dirname(__FILE__) . '/dev-tools.php');


/* ------- partie pour rediriger les abonnées vers la home page après connexion ----------- */
function acme_login_redirect($redirect_to, $request, $user)
{
    return (is_array($user->roles) && in_array('administrator', $user->roles)) ? admin_url() : site_url();
}
add_filter('login_redirect', 'acme_login_redirect', 10, 3);

// pour enlever la barre d'admin aux non administrateurs
add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar()
{
    if (! current_user_can('administrator') && ! is_admin()) {
        show_admin_bar(false);
    }
}

// pour bloquer l'accès à l'espace admin de wordpress aux non administrateurs
add_action( 'init', 'blockusers_init' );
function blockusers_init() {
    if ( is_admin() && ! current_user_can( 'administrator' ) &&
        ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            wp_redirect( home_url() );
            exit;
        }
}


