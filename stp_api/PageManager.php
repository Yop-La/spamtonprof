<?php
namespace spamtonprof\stp_api;

/*
 * Cette classe sert � g�r�r ( CRUD ) les plans de paiement paypal
 * attention un billing plan ( equivalent service/produit dans stripe ) ne peut avoir qu'un seul type de paiement definition regulier
 * ainsi ici un billing plan est �quivalent � un plan dans stp
 *
 *
 * // $host_split = explode('.',$_SERVER['HTTP_HOST']);
 * // $testMode = ($host_split[0] == 'localhost' || $host_split[0] == 'localhost:8081') && $host_split[1] == '' ? TRUE : FALSE;
 * // $domain = $testMode ? 'http://localhost/' : 'https://www.spamtonprof.com/';
 *
 *
 *
 *
 *
 */
class PageManager

{

    private $pageSlug;


    public function __construct($pageSlug)
    
    {
        $this->pageSlug = $pageSlug;
        $this->loadScripts();
        $this->handleTestMode();
    }

    public function handleTestMode()
    {
        $TestModeManager = new \spamtonprof\stp_api\TestModeManager($this->pageSlug);
        $testMode = $TestModeManager->testMode();
        $TestModeManager->initDebuger();
        $testMode = $testMode ? 'true' : 'false';
        wp_localize_script('functions_js', 'testMode', $testMode);
        wp_localize_script('functions_js', 'publicStripeKey', $TestModeManager->getPublicStripeKey());
    }

    public function loadScripts()
    {
        if ($this->pageSlug == 'abonnement-apres-essai') {
            PageManager::abonnementApresEssaiLoader();
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
}

    
    