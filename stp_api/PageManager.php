<?php
namespace spamtonprof\stp_api;

/*
 *
 * Pour charger des scripts css ou js et des variables js avant le chargement d'une page
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
        /* pour le mode test et le mode prod */
        $TestModeManager = new \spamtonprof\stp_api\TestModeManager($this->pageSlug);
        $testMode = $TestModeManager->testMode();
        $TestModeManager->initDebuger();
        $testMode = $testMode ? 'true' : 'false';
        wp_localize_script('functions_js', 'testMode', $testMode);
        wp_localize_script('functions_js', 'publicStripeKey', $TestModeManager->getPublicStripeKey());
        
        /* pour savoir si le user est loggé */
        $isLogged = is_user_logged_in();
        $isLogged = $isLogged ? 'true' : 'false';
        wp_localize_script('functions_js', 'isLogged', $isLogged);
        
        
        /* avoir le domain */
        wp_localize_script('functions_js', 'domain', $this->domain);
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
        
        if ($this->pageSlug == 'inscription-prof') {
            
            PageManager::inscriptionProf();
        }
        
        if ($this->pageSlug == 'onboarding-prof') {
            
            PageManager::onboardingProf();
        }
        
        if ($this->pageSlug == 'temoigner') {
            
            PageManager::temoigner();
        }
        
        if ($this->pageSlug == 'choisir-prof') {
            
            PageManager::choisirProf();
        }
        
        if ($this->pageSlug == 'dashboard-eleve') {
            
            PageManager::dashboardEleve();
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
        
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
    }

    public static function passwordReset()
    
    {
        wp_enqueue_script('password_reset', plugins_url() . '/spamtonprof/js/password_reset.js', array(
            
            'nf-front-end'
        
        ), time());
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
    }

    public static function logIn()
    
    {
        wp_enqueue_script('log_in', plugins_url() . '/spamtonprof/js/log_in.js', array(
            
            'nf-front-end'
        
        ), time());
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
    }

    public static function inscriptionProf()
    
    {
        wp_enqueue_script('discover_week', plugins_url() . '/spamtonprof/js/inscription-prof.js', array(
            
            'nf-front-end'
        
        ), time());
        
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
    }

    public static function onboardingProf()
    
    {
        wp_enqueue_script('stripe_main_js', 'https://js.stripe.com/v3/');
        
        wp_enqueue_script('onboarding_prof', plugins_url() . '/spamtonprof/js/onboarding-prof.js', array(
            
            'nf-front-end'
        
        ), time());
        
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
        
        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');
        
        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
        
        $current_user = wp_get_current_user();
        $profMg = new \spamtonprof\stp_api\stpProfManager();
        
        $prof = $profMg->get(array(
            'user_id_wp' => $current_user->ID
        ));
        
        if ($prof) {
            
            wp_localize_script('onboarding_prof', 'loggedProf', $prof->toArray());
            
        }
    }

    public static function temoigner()
    
    {
        wp_enqueue_script('temoigner', plugins_url() . '/spamtonprof/js/temoigner.js', array(
            
            'nf-front-end'
        
        ), time());
    }

    public static function choisirProf()
    
    {
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
        
        wp_enqueue_script('choisir_prof', plugins_url() . '/spamtonprof/js/choisir-prof.js', array(
            
            'nf-front-end'
        
        ), time());
        
        $abonnementMg = new \spamtonprof\stp_api\stpAbonnementManager();
        
        $abonnementsSansProf = $abonnementMg->getAbonnementsSansProf();
        
        wp_localize_script('choisir_prof', 'abonnementsSansProf', $abonnementsSansProf);
    }

    public static function dashboardEleve()
    
    {
        wp_enqueue_script('dashboard', plugins_url() . '/spamtonprof/js/dashboard-eleve.js', array(
            
            'nf-front-end'
        
        ), time());
        
        wp_enqueue_script('stripe_checkout_js', 'https://checkout.stripe.com/checkout.js');
        
        wp_enqueue_script('stripe_main_js', 'https://js.stripe.com/v3/');
        
        
        $current_user = wp_get_current_user();
        
        if (current_user_can('client')) {
            
            $compteMg = new \spamtonprof\stp_api\stpCompteManager();
            
            $compte = $compteMg->get(array(
                'ref_compte_wp' => $current_user->ID
            ));
            
            $abonnementMg = new \spamtonprof\stp_api\stpAbonnementManager();
            
            $abonnements = $abonnementMg->getAll(array(
                "ref_compte" => $compte->getRef_compte()
            ), array(
                "construct" => array(
                    'ref_eleve',
                    'ref_formule',
                    'ref_prof',
                    'ref_parent',
                    'ref_plan'
                ),
                "ref_eleve" => array(
                    "construct" => array(
                        'ref_classe',
                        'ref_profil'
                    )
                )
            ));
            
            $abosActif = [];
            $abosEssai = [];
            $abosTermine = [];
            
            foreach ($abonnements as $abonnement) {
                
                switch ($abonnement->getRef_statut_abonnement()) {
                    case $abonnement::ACTIF:
                        $abosActif[] = $abonnement;
                        break;
                    case $abonnement::ESSAI:
                        $abosEssai[] = $abonnement;
                        break;
                    case $abonnement::TERMINE:
                        $abosTermine[] = $abonnement;
                        break;
                }
            }
            
            wp_localize_script('dashboard', 'abosActif', $abosActif);
            wp_localize_script('dashboard', 'abosEssai', $abosEssai);
            wp_localize_script('dashboard', 'abosTermine', $abosTermine);
        }
    }
}

    
    