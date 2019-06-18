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

        /* pour savoir si le user est logg� */
        $isLogged = is_user_logged_in();

        $isLogged = ($isLogged) ? 'true' : 'false';

        $is_admin = false;
        if (current_user_can('administrator')) {
            $is_admin = true;
        }

        $is_admin = ($is_admin) ? 'true' : 'false';
        $printNum = "false";

        wp_localize_script('functions_js', 'isAdmin', $is_admin);
        wp_localize_script('functions_js', 'isLogged', $isLogged);
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

            $loggedProche = $procheMg->get(array(
                'ref_compte_wp' => $current_user->ID
            ));

            $current_user = wp_get_current_user();

            if (current_user_can('client')) {

                $compteMg = new \spamtonprof\stp_api\StpCompteManager();

                $compte = $compteMg->get(array(
                    'ref_compte_wp' => $current_user->ID
                ));

                wp_localize_script('functions_js', 'compte', $compte->toArray());

                $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

                $abonnements = $abonnementMg->getAll(array(
                    "ref_compte" => $compte->getRef_compte()
                ), array(
                    "construct" => array(
                        'ref_eleve',
                        'ref_formule',
                        'ref_prof',
                        'ref_parent',
                        'ref_plan',
                        "ref_coupon"
                    ),
                    "ref_eleve" => array(
                        "construct" => array(
                            'ref_niveau'
                        )
                    )
                ));

                $abosActif = [];
                $abosEssai = [];
                $abosTermine = [];

                $proche = false;
                $ref_proche = $compte->getRef_proche();
                if ($ref_proche) {
                    $proche = $procheMg->get((array(
                        'ref_proche' => $ref_proche
                    )));
                    wp_localize_script('functions_js', 'proche', json_decode(json_encode($proche), true));
                }

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
                ), true, array(
                    "construct" => array(
                        'ref_niveau'
                    )
                ));

                for ($i = 0; $i < count($eleves); $i ++) {

                    $eleveTemp = $eleves[$i];
                    $eleveTemp["inTrial"] = $eleveMg->isInTrial($eleveTemp["ref_eleve"]);
                    $eleves[$i] = $eleveTemp;
                }

                wp_localize_script('functions_js', 'eleves', $eleves);
            }

            wp_localize_script('functions_js', 'userType', 'autre');
            if ($loggedProche) {
                wp_localize_script('functions_js', 'userType', 'proche');
                wp_localize_script('functions_js', 'loggedProche', $loggedProche->toArray());
            }

            if ($eleve) {
                wp_localize_script('functions_js', 'userType', 'eleve');
                wp_localize_script('functions_js', 'loggedEleve', $eleve->toArray());
            }

            if ($prof) {
                wp_localize_script('functions_js', 'userType', 'prof');
                wp_localize_script('functions_js', 'loggedProf', $prof->toArray());
            }
        } else { // si pas logg� (simple visiteur)

            // $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
            // $hour = $now->format('H');
            // $pageNums = [
            // 'accueil',
            // 'tarifs',
            // 'semaine-decouverte',
            // 'decouvrir-spamtonprof',
            // 'temoignages'
            // ];

            // if ((11 <= $hour && $hour < 14) || (18 <= $hour && $hour < 20) && in_array($this->pageSlug, $pageNums)) {
            // $printNum = "true";
            // }
        }

        $numMessage = 'Vous venez de d�couvrir notre site ? Et si on en discutait au t�l�phone ? Appelez nous au 04-34-10-25-49.';
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

        if ($this->pageSlug == "accueil") {

            PageManager::acceuil();
        }

        if ($this->pageSlug == "temoignages") {

            PageManager::temoignages();
        }

        if ($this->pageSlug == "decouvrir-spamtonprof") {

            PageManager::decouvrirSpamtonprof();
        }

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

        if ($this->pageSlug == 'stage-bac') {

            PageManager::stageBac();
        }

        if ($this->pageSlug == 'stage-ete') {

            PageManager::stageEte();
        }

        if ($this->pageSlug == 'paiement') {

            PageManager::paiement();
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
        if ($this->pageSlug == 'tes-abonnements') {

            PageManager::tesAbonnements();
        }
        if ($this->pageSlug == 'gestion-formule') {

            PageManager::gestionFormule();
        }

        if ($this->pageSlug == 'ad-review') {

            PageManager::adReview();
        }

        if ($this->pageSlug == 'edit_lbc_text') {

            PageManager::editLbcText();
        }

        if ($this->pageSlug == 'espace-presse') {

            PageManager::espacePresse();
        }

        if ($this->pageSlug == 'lbc-report') {

            PageManager::lbcReport();
        }

        if ($this->pageSlug == 'facturation-prof') {
            
            PageManager::facturation_prof();
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

    public static function acceuil()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_script('acceuil_js', plugins_url() . '/spamtonprof/js/acceuil.js', array(
            'nf-front-end'
        ), time());
    }

    public static function temoignages()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_script('acceuil_js', plugins_url() . '/spamtonprof/js/temoignages.js', array(
            'nf-front-end'
        ), time());
    }

    public static function decouvrirSpamtonprof()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_script('acceuil_js', plugins_url() . '/spamtonprof/js/decouvrir-spamtonprof.js', array(
            'nf-front-end'
        ), time());
    }

    public static function lbcAdds()

    {
        wp_enqueue_script('adds_bo_js', plugins_url() . '/spamtonprof/js/lbc-adds.js', array(

            'nf-front-end'
        ), time());
    }

    public static function gestionFormule()

    {
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_script('gestion_formule_js', plugins_url() . '/spamtonprof/js/gestion-formule.js', array(

            'nf-front-end'
        ), time());
    }

    public static function stageBac()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js');

        wp_enqueue_script('paiement_js', plugins_url() . '/spamtonprof/js/stage-bac.js',array(),time());
        
        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/stage-ete.css');
    }

    public static function stageEte()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js');
        
        wp_enqueue_script('paiement_js', plugins_url() . '/spamtonprof/js/stage-ete.js',array('nf-front-end'),time());
        
        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/stage-ete.css');
    }

    public static function paiement()

    {
        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/paiement.css');

        wp_enqueue_script('paiement_js', plugins_url() . '/spamtonprof/js/paiement.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_script('stripe_checkout_js', 'https://checkout.stripe.com/checkout.js');

        wp_enqueue_script('stripe_main_js', 'https://js.stripe.com/v3/');

        if (isset($_GET['ref_formule'])) {

            $ref_formule = $_GET['ref_formule'];
            $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
            $formule = $formuleMg->get(array(
                'ref_formule' => $ref_formule
            ), array(
                "construct" => array(
                    'plans'
                )
            ));

            $dateFormuleMg = new \spamtonprof\stp_api\StpDateFormuleManager();
            $dates_formule = $dateFormuleMg->getAll(array(
                'ref_formule' => $ref_formule
            ));

            wp_localize_script('functions_js', 'formule', json_decode(json_encode($formule), true));
            wp_localize_script('functions_js', 'dates_formule', json_decode(json_encode($dates_formule), true));
        }
    }

    public static function discoverWeek()

    {
        wp_enqueue_script('discover_week', plugins_url() . '/spamtonprof/js/discover_week.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_style('css_dropdown', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css');

        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');

        wp_enqueue_script('jquery_dropdown', "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js");

        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');

        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/semaine-decouverte.css');

        // wp_enqueue_script('helper_js', "https://cdn.jsdelivr.net/npm/algoliasearch-helper@2.26.1/dist/algoliasearch.helper.min.js");

        wp_enqueue_script('algolia_js', "https://cdn.jsdelivr.net/algoliasearch/3/algoliasearch.min.js");

        wp_enqueue_script('algolia_js_auto', "https://cdn.jsdelivr.net/autocomplete.js/0/autocomplete.min.js");
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

        $abonnementsSansProf = $abonnementMg->getAbonnementsToAssign();

        wp_localize_script('choisir_prof', 'abonnementsSansProf', $abonnementsSansProf);
    }

    public static function dashboardEleve()

    {
        wp_enqueue_script('dashboard', plugins_url() . '/spamtonprof/js/dashboard-eleve.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_script('stripe_checkout_js', 'https://checkout.stripe.com/checkout.js');

        wp_enqueue_script('stripe_main_js', 'https://js.stripe.com/v3/');

        wp_enqueue_style('ds_eleve_css', get_stylesheet_directory_uri() . '/css/pages/dashboard-eleve.css');
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
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_script('tarifs', plugins_url() . '/spamtonprof/js/tarifs.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/tarifs.css');

        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/semaine-decouverte.css');

        wp_enqueue_script('algolia_js', "https://cdn.jsdelivr.net/algoliasearch/3/algoliasearch.min.js");

        wp_enqueue_script('algolia_js_auto', "https://cdn.jsdelivr.net/autocomplete.js/0/autocomplete.min.js");
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

    public static function tesAbonnements()
    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/tes-abonnements.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/tes-abonnements.js', array(

            'nf-front-end'
        ), time());
    }

    public static function adReview()

    {
        wp_enqueue_script('tarifs', plugins_url() . '/spamtonprof/js/ad-review.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/ad-review.css');
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
    }

    public static function editLbcText()

    {
        wp_enqueue_script('tarifs', plugins_url() . '/spamtonprof/js/edit_lbc_text.js', array(

            'nf-front-end'
        ), time());

        // wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/ad-review.css');
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
    }

    public static function espacePresse()

    {
        wp_enqueue_style('css_espace-presse', get_home_url() . '/wp-content/themes/salient-child/css/pages/espace-presse.css');
    }


    public static function facturation_prof()
    
    {
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
        
        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/facturation-prof.js', array(
            
            'nf-front-end'
        ), time());
        
    }

    public static function lbcReport()

    {
        wp_enqueue_script('data_table_js', "https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js", array(

            'nf-front-end'
        ), time());

        wp_enqueue_script('js', plugins_url() . '/spamtonprof/js/lbc-report.js', array(

            'nf-front-end'
        ), time());

        // wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/ad-review.css');
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_style('css_data_table', "https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css");
    }
}



    

    