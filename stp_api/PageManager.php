<?php
namespace spamtonprof\stp_api;

/*
 *
 * Cette classe sert � g�r�r ( CRUD ) les plans de paiement paypal
 *
 * attention un billing plan ( equivalent service/produit dans stripe ) ne peut avoir qu'un seul type de paiement definition regulier
 *
 * ainsi ici un billing plan est �quivalent � un plan dans stp
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
class PageManager

{

    private $pageSlug, $domain;

    public function __construct($pageSlug)
    
    {
        $this->pageSlug = $pageSlug;
        
        $this->loadScripts();
        
        $host_split = explode('.', $_SERVER['HTTP_HOST']);
        
        $this->domain = $host_split[0];
        
        $this->loadSessionVariable();
        
    }

    public function loadSessionVariable()
    
    {
        $TestModeManager = new \spamtonprof\stp_api\TestModeManager($this->pageSlug);
        
        $testMode = $TestModeManager->testMode();
        
        $TestModeManager->initDebuger();
        
        $isLogged = is_user_logged_in();
        
        $testMode = $testMode ? 'true' : 'false';
        
        $isLogged = $isLogged ? 'true' : 'false';
        
        wp_localize_script('functions_js', 'testMode', $testMode);
        
        wp_localize_script('functions_js', 'isLogged', $isLogged);
        
        wp_localize_script('functions_js', 'domain', $this->domain);
        
        wp_localize_script('functions_js', 'publicStripeKey', $TestModeManager->getPublicStripeKey());
    }

    public function loadScripts()
    
    {
        wp_enqueue_script('functions_js', plugins_url() . '/spamtonprof/js/functions.js');
        
        wp_enqueue_script('log_out_js', plugins_url() . '/spamtonprof/js/log_out.js');
        
        wp_localize_script('functions_js', 'homeUrl', get_home_url());
        
        wp_localize_script('functions_js', 'ajaxurl', admin_url('admin-ajax.php'));
        
        wp_localize_script('functions_js', 'currentSlug', $this->pageSlug);
        
        if ($this->pageSlug == 'abonnement-apres-essai') {
            
            PageManager::abonnementApresEssaiLoader();
        }
        
        if ($this->pageSlug == 'inscription-essai-eleve') {
            
            PageManager::inscriptionEssaiEleve();
        }
        
        if ($this->pageSlug == 'inscription-essai-parent') {
            
            PageManager::inscriptionEssaiEleve();
        }
        
        if ($this->pageSlug == 'lbc-adds') {
            
            PageManager::lbcAdds();
        }
        
        if ($this->pageSlug == 'semaine-decouverte') {
            
            PageManager::discoverWeek();
        }
        
        if ($this->pageSlug == 'reset-password') {
            
            PageManager::passwordReset();
        }
        
        if ($this->pageSlug == 'connexion') {
            
            PageManager::logIn();
        }
        
    }

    public static function abonnementApresEssaiLoader()
    
    {
        wp_enqueue_script('get_trial_account_js', plugins_url() . '/spamtonprof/js/paiement-complet.js', array(
            
            'nf-front-end'
        
        ), time());
        
        wp_enqueue_script('stripe_checkout_js', 'https://checkout.stripe.com/checkout.js');
        
        wp_enqueue_script('stripe_main_js', 'https://js.stripe.com/v3/');
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
        
        wp_enqueue_script('font_awesome_css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
        
        wp_enqueue_script('checkout_paypal_js', 'https://www.paypalobjects.com/api/checkout.js');
    }

    public static function inscriptionEssaiEleve()
    
    {
        wp_enqueue_script('inscription-essai_js', plugins_url() . '/spamtonprof/js/inscription-essai-eleve.js', array(
            
            'nf-front-end'
        
        ), time());
    }

    public static function lbcAdds()
    
    {
        wp_enqueue_script('adds_bo_js', plugins_url() . '/spamtonprof/js/lbc-adds.js', array(
            
            'nf-front-end'
        
        ), time());
    }
    
    public static function discoverWeek()
    
    {
        wp_enqueue_script('discover_week', plugins_url() . '/spamtonprof/js/discover_week.js', array(
            
            'nf-front-end'
            
        ), time());
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
//         wp_enqueue_style('popup_css', get_home_url() . '/wp-content/themes/salient-child/css/popup/inscription-essai.css');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
        
    }
    
    
    public static function passwordReset()
    
    {
        wp_enqueue_script('password_reset', plugins_url() . '/spamtonprof/js/password_reset.js', array(
            
            'nf-front-end'
            
        ), time());
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
//         wp_enqueue_style('popup_css', get_home_url() . '/wp-content/themes/salient-child/css/popup/inscription-essai.css');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
        
    }
    
    public static function logIn()
    
    {
        wp_enqueue_script('log_in', plugins_url() . '/spamtonprof/js/log_in.js', array(
            
            'nf-front-end'
            
        ), time());
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
        //         wp_enqueue_style('popup_css', get_home_url() . '/wp-content/themes/salient-child/css/popup/inscription-essai.css');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
        
    }
}

    
    