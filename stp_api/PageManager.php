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

    private $pageSlug, $domain, $stpAccount = false, $pagesVariables = [];

    public function __construct($pageSlug)

    {
        $this->pageSlug = $pageSlug;

        $this->setCommonPageVariables();

        $this->loadScripts();

        $host_split = explode('.', $_SERVER['HTTP_HOST']);

        $this->domain = $host_split[0];

        $this->pagesVariables['domain'] = $this->domain;
        
        $_SESSION["domain"] = $this->domain;

        $this->loadVariablesOnPages();
    }

    public function loadVariablesOnPages()
    {
        foreach ($this->pagesVariables as $key => $value) {
            wp_localize_script('functions_js', $key, $value);
        }
    }

    public function setCommonPageVariables()

    {

        /* pour le mode test et le mode prod */
        $TestModeManager = new \spamtonprof\stp_api\TestModeManager($this->pageSlug);
        $testMode = $TestModeManager->testMode();
        $TestModeManager->initDebuger();
        $testMode = $testMode ? 'true' : 'false';

        // wp_localize_script('functions_js', 'testMode', $testMode);
        // wp_localize_script('functions_js', 'publicStripeKey', $TestModeManager->getPublicStripeKey());

        $this->pagesVariables['testMode'] = $testMode;
        $this->pagesVariables['publicStripeKey'] = $TestModeManager->getPublicStripeKey();

        /* pour savoir si le user est logg� */
        $isLogged = is_user_logged_in();

        $isLogged = ($isLogged) ? 'true' : 'false';

        $is_admin = false;
        if (current_user_can('administrator')) {
            $is_admin = true;
        }

        $is_admin = ($is_admin) ? 'true' : 'false';
        $printNum = "false";

        // wp_localize_script('functions_js', 'isAdmin', $is_admin);
        // wp_localize_script('functions_js', 'isLogged', $isLogged);

        $this->pagesVariables['isAdmin'] = $is_admin;
        $this->pagesVariables['isLogged'] = $isLogged;

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

                $this->stpAccount = $compte;

                // wp_localize_script('functions_js', 'compte', $compte->toArray());
                $this->pagesVariables['compte'] = $compte->toArray();

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
                    // wp_localize_script('functions_js', 'proche', json_decode(json_encode($proche), true));
                    $this->pagesVariables['proche'] = json_decode(json_encode($proche), true);
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

                // wp_localize_script('functions_js', 'abosActif', $abosActif);
                // // wp_localize_script('functions_js', 'abosEssai', $abosEssai);
                // wp_localize_script('functions_js', 'abosTermine', $abosTermine);

                $this->pagesVariables['abosActif'] = $abosActif;
                $this->pagesVariables['abosEssai'] = $abosEssai;
                $this->pagesVariables['abosTermine'] = $abosTermine;

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

                // wp_localize_script('functions_js', 'eleves', $eleves);

                $this->pagesVariables['eleves'] = $eleves;
            }

            // wp_localize_script('functions_js', 'userType', 'autre');

            $this->pagesVariables['userType'] = 'autre';

            if ($loggedProche) {
                // wp_localize_script('functions_js', 'userType', 'proche');
                // wp_localize_script('functions_js', 'loggedProche', $loggedProche->toArray());

                $this->pagesVariables['userType'] = 'proche';
                $this->pagesVariables['loggedProche'] = $loggedProche->toArray();
            }

            if ($eleve) {
                // wp_localize_script('functions_js', 'userType', 'eleve');
                // wp_localize_script('functions_js', 'loggedEleve', $eleve->toArray());

                $this->pagesVariables['userType'] = 'eleve';
                $this->pagesVariables['loggedEleve'] = $eleve->toArray();
            }

            if ($prof) {

                $this->pagesVariables['userType'] = 'prof';
                $this->pagesVariables['loggedProf'] = $prof->toArray();

                // wp_localize_script('functions_js', 'userType', 'prof');
                // wp_localize_script('functions_js', 'loggedProf', $prof->toArray());
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

        /* avoir le domain */

        $this->pagesVariables['domain'] = $this->domain;

        // wp_localize_script('functions_js', 'domain', $this->domain);
    }

    public function loadScripts()
    {
        wp_enqueue_script('functions_js', plugins_url() . '/spamtonprof/js/functions.js');

        wp_enqueue_script('log_out_js', plugins_url() . '/spamtonprof/js/log_out.js');

        wp_localize_script('functions_js', 'homeUrl', get_home_url());

        wp_localize_script('functions_js', 'ajaxurl', admin_url('admin-ajax.php'));

        wp_localize_script('functions_js', 'currentSlug', $this->pageSlug);

        if ($this->pageSlug == "accueil") {

            $this->acceuil();
        }

        if ($this->pageSlug == "temoignages") {

            $this->temoignages();
        }

        if ($this->pageSlug == "decouvrir-spamtonprof") {

            $this->decouvrirSpamtonprof();
        }

        if ($this->pageSlug == 'abonnement-apres-essai') {

            $this->abonnementApresEssaiLoader();
        }

        if ($this->pageSlug == 'inscription-essai-eleve') {

            $this->inscriptionEssaiEleve();
        }

        if ($this->pageSlug == 'inscription-essai-parent') {

            $this->inscriptionEssaiEleve();
        }

        if ($this->pageSlug == 'lbc-adds') {

            $this->lbcAdds();
        }

        if ($this->pageSlug == 'semaine-decouverte') {

            $this->discoverWeek();
        }

        if ($this->pageSlug == 'stage-bac') {

            $this->stageBac();
        }

        if ($this->pageSlug == 'stage-ete') {

            $this->stageEte();
        }

        if ($this->pageSlug == 'paiement') {

            $this->paiement();
        }

        if ($this->pageSlug == 'reset-password') {

            $this->passwordReset();
        }

        if ($this->pageSlug == 'connexion') {

            $this->logIn();
        }

        if ($this->pageSlug == 'tarifs') {

            $this->tarifs();
        }

        if ($this->pageSlug == 'inscription-prof') {

            $this->inscriptionProf();
        }

        if ($this->pageSlug == 'onboarding-prof') {

            $this->onboardingProf();
        }

        if ($this->pageSlug == 'temoigner') {

            $this->temoigner();
        }

        if ($this->pageSlug == 'choisir-prof') {

            $this->choisirProf();
        }

        if ($this->pageSlug == 'dashboard-eleve') {

            $this->dashboardEleve();
        }

        if ($this->pageSlug == 'dashboard-prof') {

            $this->dashboardProf();
        }

        if ($this->pageSlug == 'back-office') {

            $this->backOffice();
        }

        if ($this->pageSlug == 'formule') {

            $this->formule();
        }

        if ($this->pageSlug == 'reporting-lbc') {

            $this->reportingLbc();
        }
        if ($this->pageSlug == 'tes-abonnements') {

            $this->tesAbonnements();
        }
        if ($this->pageSlug == 'gestion-formule') {

            $this->gestionFormule();
        }

        if ($this->pageSlug == 'ad-review') {

            $this->adReview();
        }

        if ($this->pageSlug == 'edit_lbc_text') {

            $this->editLbcText();
        }

        if ($this->pageSlug == 'espace-presse') {

            $this->espacePresse();
        }

        if ($this->pageSlug == 'lbc-report') {

            $this->lbcReport();
        }

        if ($this->pageSlug == 'facturation-prof') {

            $this->facturation_prof();
        }
    }

    public function abonnementApresEssaiLoader()

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

    public function inscriptionEssaiEleve()

    {
        wp_enqueue_script('inscription-essai_js', plugins_url() . '/spamtonprof/js/inscription-essai-eleve.js', array(

            'nf-front-end'
        ), time());
    }

    public function acceuil()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_script('acceuil_js', plugins_url() . '/spamtonprof/js/acceuil.js', array(
            'nf-front-end'
        ), time());
    }

    public function temoignages()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_script('acceuil_js', plugins_url() . '/spamtonprof/js/temoignages.js', array(
            'nf-front-end'
        ), time());
    }

    public function decouvrirSpamtonprof()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_script('acceuil_js', plugins_url() . '/spamtonprof/js/decouvrir-spamtonprof.js', array(
            'nf-front-end'
        ), time());
    }

    public function lbcAdds()

    {
        wp_enqueue_script('adds_bo_js', plugins_url() . '/spamtonprof/js/lbc-adds.js', array(

            'nf-front-end'
        ), time());
    }

    public function gestionFormule()

    {
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_script('gestion_formule_js', plugins_url() . '/spamtonprof/js/gestion-formule.js', array(

            'nf-front-end'
        ), time());
    }

    public function stageBac()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js');

        wp_enqueue_script('paiement_js', plugins_url() . '/spamtonprof/js/stage-bac.js', array(), time());

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/stage-ete.css');
    }

    public function stageEte()

    {
        wp_enqueue_script('countdown_js', plugins_url() . '/spamtonprof/js/jquery.countdown-2.2.0/jquery.countdown.min.js');

        wp_enqueue_script('paiement_js', plugins_url() . '/spamtonprof/js/stage-ete.js', array(
            'nf-front-end'
        ), time());

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/stage-ete.css');
    }

    public function paiement()

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

    public function discoverWeek()

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

    public function passwordReset()

    {
        wp_enqueue_script('password_reset', plugins_url() . '/spamtonprof/js/password_reset.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');

        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
    }

    public function logIn()

    {
        wp_enqueue_script('log_in', plugins_url() . '/spamtonprof/js/log_in.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');

        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
    }

    public function inscriptionProf()

    {
        wp_enqueue_script('discover_week', plugins_url() . '/spamtonprof/js/inscription-prof.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_script('jquery_ui_js', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.js');

        wp_enqueue_script('jquery_ui_css', plugins_url() . '/spamtonprof/js/jquery-ui-1.12.1.custom/jquery-ui.min.css');
    }

    public function onboardingProf()

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

    public function temoigner()

    {
        wp_enqueue_script('temoigner', plugins_url() . '/spamtonprof/js/temoigner.js', array(

            'nf-front-end'
        ), time());
    }

    public function choisirProf()

    {
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_script('choisir_prof', plugins_url() . '/spamtonprof/js/choisir-prof.js', array(

            'nf-front-end'
        ), time());

        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

        $abonnementsSansProf = $abonnementMg->getAbonnementsToAssign();

        wp_localize_script('choisir_prof', 'abonnementsSansProf', $abonnementsSansProf);
    }

    public function dashboardEleve()

    {
        wp_enqueue_script('dashboard', plugins_url() . '/spamtonprof/js/dashboard-eleve.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_script('stripe_checkout_js', 'https://checkout.stripe.com/checkout.js');

        wp_enqueue_script('stripe_main_js', 'https://js.stripe.com/v3/');

        wp_enqueue_style('ds_eleve_css', get_stylesheet_directory_uri() . '/css/pages/dashboard-eleve.css');
        
        
        $interruptionMg = new \spamtonprof\stp_api\StpInterruptionManager();

        $stpAct = $this->stpAccount;

        if ($stpAct) {

            $constructor = array(
                "construct" => array(
                    'ref_abonnement'
                ),
                "ref_abonnement" => array(
                    "construct" => array(
                        'ref_eleve',
                        'ref_formule'
                    )
                )
            );

            // on récupère les interruptions
            $interruptions = $interruptionMg->getAll(array(
                'key' => 'of_an_account',
                'params' => [
                    'ref_compte' => $stpAct->getRef_compte()
                ]
            ), $constructor);
            
            foreach ($interruptions as $interruption) {

                $debut = $interruption->getDebut();
                $debut = \DateTime::createFromFormat(PG_DATE_FORMAT, $debut);
                $debut = $debut->format(FR_DATE_FORMAT);

                $fin = $interruption->getFin();
                $fin = \DateTime::createFromFormat(PG_DATE_FORMAT, $fin);
                $fin = $fin->format(FR_DATE_FORMAT);

                $interruption->setDebut($debut);
                $interruption->setFin($fin);
            }


            $this->pagesVariables['interruptions'] = json_decode(json_encode($interruptions), true);
            
            
            $onglet = "0";
            if(array_key_exists('onglet', $_GET)){
                $onglet = $_GET['onglet'];
                
            }
            $this->pagesVariables['onglet'] = $onglet;
            
        }
    }

    public function backOffice()

    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/back-office.css');

        // wp_enqueue_script('helper_js', "https://cdn.jsdelivr.net/npm/algoliasearch-helper@2.26.1/dist/algoliasearch.helper.min.js");

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/algoliasearch@3.35.1/dist/algoliasearchLite.min.js');

        wp_enqueue_script('instant_seach_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@4.0.0/dist/instantsearch.production.min.js');

        wp_enqueue_script('dashboard', plugins_url() . '/spamtonprof/js/back-office.js', array(

            'nf-front-end'
        ), time());
    }

    public function dashboardProf()

    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('dashboard_css', get_stylesheet_directory_uri() . '/css/pages/dashboard-prof.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('dashboard', plugins_url() . '/spamtonprof/js/dashboard-prof.js', array(

            'nf-front-end'
        ), time());
    }

    public function tarifs()

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

    public function formule()

    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/formule.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/formule.js', array(

            'nf-front-end'
        ), time());
    }

    public function reportingLbc()
    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/reporting-lbc.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/reporting-lbc.js', array(

            'nf-front-end'
        ), time());
    }

    public function tesAbonnements()
    {
        wp_enqueue_style('algolia_css', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.css');

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/tes-abonnements.css');

        wp_enqueue_script('algolia_js', 'https://cdn.jsdelivr.net/npm/instantsearch.js@2.3/dist/instantsearch.min.js');

        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/tes-abonnements.js', array(

            'nf-front-end'
        ), time());
    }

    public function adReview()

    {
        wp_enqueue_script('tarifs', plugins_url() . '/spamtonprof/js/ad-review.js', array(

            'nf-front-end'
        ), time());

        wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/ad-review.css');
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
    }

    public function editLbcText()

    {
        wp_enqueue_script('tarifs', plugins_url() . '/spamtonprof/js/edit_lbc_text.js', array(

            'nf-front-end'
        ), time());

        // wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/ad-review.css');
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
    }

    public function espacePresse()

    {
        wp_enqueue_style('css_espace-presse', get_home_url() . '/wp-content/themes/salient-child/css/pages/espace-presse.css');
    }

    public function facturation_prof()

    {
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');

        wp_enqueue_script('formule_js', plugins_url() . '/spamtonprof/js/facturation-prof.js', array(

            'nf-front-end'
        ), time());
    }

    public function lbcReport()

    {
        wp_enqueue_script('data_table_js', "https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js", array(

            'nf-front-end'
        ), time());

        wp_enqueue_script('js', plugins_url() . '/spamtonprof/js/lbc-report.js', array(

            'nf-front-end'
        ), time());

        // wp_enqueue_style('bo_css', get_stylesheet_directory_uri() . '/css/pages/ad-review.css');
        wp_enqueue_style('css_form', get_home_url() . '/wp-content/themes/salient-child/css/form/inscription-essai.css');
    }
}



    

    