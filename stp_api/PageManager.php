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

        $_SESSION["domain"] = $this->domain;
        
        $_SESSION["prod"] = true;
        if( strpos($_SESSION["domain"], 'localhost') !== false){
            $_SESSION["prod"] = false;
        }
            

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

        $isLogged = ($isLogged) ? 'true' : 'false';

        wp_localize_script('functions_js', 'isLogged', $isLogged);
        $printNum = "false";

        /* pour connaitre le type de user : prof, eleve, proche, autre */
        $current_user = wp_get_current_user();

        if ($isLogged == "true") {

            $procheMg = new \spamtonprof\stp_api\StpProcheManager();
            $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
            $profMg = new \spamtonprof\stp_api\StpProfManager();

            $prof = $profMg->get(array(
                'user_id_wp' => $current_user->ID
            ));

            $eleve = $eleveMg->get(array(
                'ref_compte_wp' => $current_user->ID
            ));

            $proche = $procheMg->get(array(
                'ref_compte_wp' => $current_user->ID
            ));

            $current_user = wp_get_current_user();

            if (current_user_can('client')) {

                $compteMg = new \spamtonprof\stp_api\StpCompteManager();

                $compte = $compteMg->get(array(
                    'ref_compte_wp' => $current_user->ID
                ));

                $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

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

                wp_localize_script('functions_js', 'abosActif', $abosActif);
                wp_localize_script('functions_js', 'abosEssai', $abosEssai);
                wp_localize_script('functions_js', 'abosTermine', $abosTermine);

                $eleves = $eleveMg->getAll(array(
                    "ref_compte" => $compte->getRef_compte()
                ), true);

                for ($i = 0; $i < count($eleves); $i ++) {

                    $eleveTemp = $eleves[$i];
                    $eleveTemp["inTrial"] = $eleveMg->isInTrial($eleveTemp["ref_eleve"]);
                    $eleves[$i] = $eleveTemp;
                }

                wp_localize_script('functions_js', 'eleves', $eleves);
            }

            wp_localize_script('functions_js', 'userType', 'autre');
            if ($proche) {

                wp_localize_script('functions_js', 'userType', 'proche');
                wp_localize_script('functions_js', 'loggedProche', $proche->toArray());
            }

            if ($eleve) {
                wp_localize_script('functions_js', 'userType', 'eleve');
                wp_localize_script('functions_js', 'loggedEleve', $eleve->toArray());
            }

            if ($prof) {
                wp_localize_script('functions_js', 'userType', 'prof');
                wp_localize_script('functions_js', 'loggedProf', $prof->toArray());
            }
        } else {

            $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
            $hour = $now->format('H');
            $pageNums = [
                'accueil',
                'tarifs',
                'semaine-decouverte',
                'decouvrir-spamtonprof',
                'temoignages'
            ];

            if ((11 <= $hour && $hour < 14) || (18 <= $hour && $hour < 20) && in_array($this->pageSlug, $pageNums)) {
                $printNum = "true";
            }
        }

        $numMessage = 'Vous venez de découvrir notre site ? Et si on en discutait au téléphone ? Appelez nous au 04-34-10-25-49.';
        wp_localize_script('functions_js', 'numMessage', array(
            'message' => utf8_encode($numMessage),
            'print' => $printNum
        ));

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

        if ($this->pageSlug == 'tarifs') {

            PageManager::tarifs();
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

        if ($this->pageSlug == 'dashboard-prof') {

            PageManager::dashboardProf();
        }

        if ($this->pageSlug == 'back-office') {

            PageManager::backOffice();
        }

        if ($this->pageSlug == 'formule') {

            PageManager::formule();
        }

        if ($this->pageSlug == 'reporting-lbc') {

            PageManager::reportingLbc();
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

        wp_enqueue_style('css_dropdown', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css');

        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');

        wp_enqueue_script('jquery_dropdown', "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js");

        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');

        $classeMg = new \spamtonprof\stp_api\StpClasseManager();

        $classes = $classeMg->getAll("byprofil");
        wp_localize_script('discover_week', 'classesByProfil', $classes);
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
        $profMg = new \spamtonprof\stp_api\StpProfManager();

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

        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

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
    }

    public static function backOffice()

    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/back-office.css');

        // wp_enqueue_script('helper_js', "https://cdn.jsdelivr.net/npm/algoliasearch-helper@2.26.1/dist/algoliasearch.helper.min.js");

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('dashboard', plugins_url() . '/spamtonprof/js/back-office.js', array(

            'nf-front-end'
        ), time());
    }

    public static function dashboardProf()

    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('dashboard_css', get_stylesheet_directory_uri() . '/css/pages/dashboard-prof.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('dashboard', plugins_url() . '/spamtonprof/js/dashboard-prof.js', array(

            'nf-front-end'
        ), time());
    }

    public static function tarifs()

    {
        wp_enqueue_script('tarifs', plugins_url() . '/spamtonprof/js/tarifs.js', array(

            'nf-front-end'
        ), time());
    }

    public static function formule()

    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/formule.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/formule.js', array(

            'nf-front-end'
        ), time());
    }

    public static function reportingLbc()

    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/reporting-lbc.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/reporting-lbc.js', array(

            'nf-front-end'
        ), time());
    }
}

    
    