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
 * Version: 1.1.9.4.6
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

require_once (dirname(__FILE__) . '/inc/autoloader_stripe.php'); // autoloader des classes stripe

require_once (dirname(__FILE__) . '/inc/autoloader_getresponse.php'); // autoloader les custom classes de getresponse

require_once (dirname(__FILE__) . '/inc/autoloader_google.php'); // autoloader les custom classes de google

require_once (dirname(__FILE__) . '/inc/autoloader_cnl.php'); // autoloader les custom classes de cnl

require_once (dirname(__FILE__) . '/inc/autoloader_lbc.php'); // autoloader les custom classes de lbc

require_once (dirname(__FILE__) . '/shortcode/shortcodes.php');

require_once (dirname(__FILE__) . '/vendor/autoload.php'); // autoload strip, paypal , gmail

require_once (dirname(__FILE__) . '/vendor/getresponse/GetResponseAPI3.class.php');

require_once (dirname(__FILE__) . '/vendor/mxforward/mx_forward.php');

require_once (dirname(__FILE__) . '/slack/Slack.php'); // pour communiquer avec slack

require_once (dirname(__FILE__) . '/dev-tools.php');

/* require tous les fichiers contenant des fonctions ajax */

require_once (dirname(__FILE__) . '/ajaxFunction/page-inscription-essai_eleve-ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/adds_back_office_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/discover_week_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/log_in_and_out.php');

require_once (dirname(__FILE__) . '/ajaxFunction/inscription_prof_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/onboarding_prof_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/choisir-prof.php');

require_once (dirname(__FILE__) . '/ajaxFunction/ajax_dashboard_eleve.php');

require_once (dirname(__FILE__) . '/ajaxFunction/ajax_bo.php');

require_once (dirname(__FILE__) . '/ajaxFunction/gestion_formule_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/ads_review.php');

require_once (dirname(__FILE__) . '/ajaxFunction/edit_lbc_text.php');

require_once (dirname(__FILE__) . '/ajaxFunction/lbc-report.php');

require_once (dirname(__FILE__) . '/ajaxFunction/paiement_ajax.php');

require_once (dirname(__FILE__) . '/ajaxFunction/facturation-prof.php');

require_once (dirname(__FILE__) . '/ajaxFunction/ajax_offre_spam_express.php');

add_action('template_redirect', 'handleRedirections');

function is_test_user()
{
    $user = wp_get_current_user();
    if ($user) {
        if (strpos($user->user_email, 'yopla.33mail') !== false) {
            return (true);
        }
    }
    return (false);
}

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

add_action('wp_enqueue_scripts', 'theme_enqueue_styles', PHP_INT_MAX);

function theme_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/styles/child-style.css', array(
        'parent-style'
    ));
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

    // target "choisir prof" field in choisir_prof
    if ($settings['key'] == 'choisir_le_prof_1533217231976') {

        $StpProfMg = new \spamtonprof\stp_api\StpProfManager();

        $profs = $StpProfMg->getAll(array(
            "inbox_ready" => true
        ));

        foreach ($profs as $prof) {

            $prof = $StpProfMg->cast($prof);

            $options[] = array(
                'label' => $prof->getPrenom() . " " . $prof->getNom(),
                'value' => $prof->getRef_prof()
            );
        }

        wp_reset_postdata();
    }

    // target "Les niveaux" du formulaire "Editer une formule"
    if ($settings['key'] == 'selectionner_le_ou_les_niveaux_1541623754266') {

        $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();

        $niveaux = $niveauMg->getAll(array(
            'all'
        ));

        foreach ($niveaux as $niveau) {

            $options[] = array(
                'label' => $niveau->getNiveau(),
                'value' => $niveau->getSigle()
            );
        }
    }

    // target "Les matieres" du formulaire "Editer une formule"
    if ($settings['key'] == 'listmultiselect_1541622233707') {

        $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();

        $matieres = $matiereMg->getAll(array(
            'all'
        ));

        foreach ($matieres as $matiere) {

            $options[] = array(
                'label' => $matiere->getMatiere_complet(),
                'value' => $matiere->getMatiere()
            );
        }
    }

    // target "Les formules" du formulaire "Editer une formule"
    if ($settings['key'] == 'choisir_la_formule_a_editer_1541544041713') {

        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

        $formules = $formuleMg->getAll(array(
            'from_tool' => true
        ));

        foreach ($formules as $formule) {

            $options[] = array(
                'label' => $formule->getFormule(),
                'value' => $formule->getRef_formule()
            );
        }
    }

    // target "choisir eleve" du formulaire "inscription essai"
    if ($settings['key'] == 'choix_eleve_1542061024363') {

        if (is_user_logged_in()) {

            if (current_user_can('client')) {

                $current_user = wp_get_current_user();

                $compteMg = new \spamtonprof\stp_api\StpCompteManager();

                $compte = $compteMg->get(array(
                    'ref_compte_wp' => $current_user->ID
                ));

                $eleveMg = new \spamtonprof\stp_api\StpEleveManager();

                $eleves = $eleveMg->getAll(array(
                    'ref_compte' => $compte->getRef_compte()
                ));

                foreach ($eleves as $eleve) {

                    $options[] = array(
                        'label' => $eleve->getPrenom() . ' ' . $eleve->getNom(),
                        'value' => $eleve->getRef_eleve()
                    );
                }
            }
        }
    }

    // target "choix client leboncoin" du formulaire "Selectionner client leboncoin"
    if ($settings['key'] == 'choisir_un_client_leboncoin_1542472364642' || $settings['key'] == 'choisir_client_1542481215387') {

        if (is_user_logged_in()) {

            $clientMg = new \spamtonprof\stp_api\LbcClientManager();

            $clients = $clientMg->getAll(array(
                'all'
            ));

            foreach ($clients as $client) {

                $options[] = array(
                    'label' => $client->getPrenom_client() . ' ' . $client->getNom_client() . ' ref : ' . $client->getRef_client(),
                    'value' => $client->getRef_client()
                );
            }
        }
    }

    // target "choix demande" du formulaire "commande spamexpress"
    if ($settings['key'] == 'choix_demande_1591536866148') {

        if (array_key_exists("cmd_spam_express_step1", $_SESSION)) {

            $cmd = $_SESSION["cmd_spam_express_step1"];

            $cmd = \spamtonprof\stp_api\StpCmdSpamExpressManager::cast($cmd);

            $offres = $cmd->getOffres();

            foreach ($offres as $offre) {

                $offre = \spamtonprof\stp_api\StpOffreSpamExpressManager::cast($offre);

                if ($offre->getMain()) {

                    $options[] = array(
                        'label' => $offre->getName(),
                        'value' => $offre->getRef_offre()
                    );
                }
            }
        }
    }

    // target "choix niveau" du formulaire "commande spamexpress"
    if ($settings['key'] == 'choix_niveau_1591536885566') {

        $catMg = new \spamtonprof\stp_api\StpCategorieScolaireManager();

        $cats = $catMg->getAll();

        foreach ($cats as $cat) {

            $options[] = array(
                'label' => $cat->getName(),
                'value' => $cat->getRef_cat_scolaire()
            );
        }
    }

    // target "choix matiere" du formulaire "commande spamexpress"
    if ($settings['key'] == 'choix_matiere_1591536866148') {

        $poleMg = new \spamtonprof\stp_api\StpPoleManager();

        $poles = $poleMg->getAll();

        foreach ($poles as $pole) {

            $options[] = array(
                'label' => $pole->getName(),
                'value' => $pole->getRef_pole()
            );
        }
    }

    // target "choix type titre" du formulaire "conf client leboncoin"
    if ($settings['key'] == 'type_titre_1542480076396') {

        if (is_user_logged_in()) {

            $typeTitleMg = new \spamtonprof\stp_api\TypeTitreManager();

            $typeTitles = $typeTitleMg->getAll(array(
                'all'
            ));

            foreach ($typeTitles as $typeTitle) {

                $options[] = array(
                    'label' => $typeTitle->getType(),
                    'value' => $typeTitle->getRef_type()
                );
            }
        }
    }

    // target "choix type texte" et "reponse_lbc" du formulaire "conf client leboncoin"
    if ($settings['key'] == 'type_texte_1542480094564' || $settings['key'] == 'text_category_1546779674338' || $settings['key'] == 'listselect_1547763879269' || $settings['key'] == 'listselect_1547763974005' || $settings['key'] == 'listselect_1548592246093') {

        if (is_user_logged_in()) {

            $typeTexteMg = new \spamtonprof\stp_api\TypeTexteManager();

            $typeTextes = $typeTexteMg->getAll(array(
                'all'
            ));

            foreach ($typeTextes as $typeTexte) {

                $options[] = array(
                    'label' => $typeTexte->getType() . ' : ' . $typeTexte->getRef_type(),
                    'value' => $typeTexte->getRef_type()
                );
            }
        }
    }

    // target "choix domain" du formulaire "conf client leboncoin"
    if ($settings['key'] == 'listselect_1544217682738' || $settings['key'] == 'listselect_1544218848651') {

        if (is_user_logged_in()) {

            $domainMg = new \spamtonprof\stp_api\StpDomainManager();

            $domains = $domainMg->getAll(array(
                'in_black_list' => false,
                'mx_ok' => true,
                'disabled' => false
            ));

            foreach ($domains as $domain) {

                $options[] = array(
                    'label' => $domain->getName(),
                    'value' => $domain->getName()
                );
            }
        }
    }

    // target "choix domain" du formulaire "conf client leboncoin"
    if ($settings['key'] == 'choix-niveau_1559321703046') {

        $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();

        $niveaux = $niveauMg->getAll(array(
            'all'
        ));

        foreach ($niveaux as $niveau) {

            $options[] = array(
                'label' => $niveau->getNiveau(),
                'value' => $niveau->getRef_niveau()
            );
        }
    }

    // target "prenom_lbc" du formulaire "conf client leboncoin"
    if (($settings['key'] == 'listselect_1560173067982' && LOCAL) || $settings['key'] == 'listselect_1560177320096' && ! LOCAL) {

        $niveauMg = new \spamtonprof\stp_api\PrenomLbcManager();

        $cats = $niveauMg->getAllCat();

        foreach ($cats as $cat) {

            $options[] = array(
                'value' => $cat,
                'label' => $cat
            );
        }
    }

    // target "ref_formule" du formulaire "ajout interruption"
    if ($settings['key'] == 'ref_formule_1575352684285') {

        if (is_user_logged_in()) {

            if (current_user_can('client')) {

                $current_user = wp_get_current_user();

                $compteMg = new \spamtonprof\stp_api\StpCompteManager();
                $compte = $compteMg->get(array(
                    'ref_compte_wp' => $current_user->ID
                ));

                $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

                $constructor = array(
                    "construct" => array(
                        'ref_eleve',
                        'ref_formule'
                    )
                );

                $abos = $aboMg->getAll(array(
                    'key' => 'all_actif_abos_of_account',
                    'ref_compte' => $compte->getRef_compte()
                ), $constructor);

                foreach ($abos as $abo) {

                    $ref_abo = $abo->getRef_abonnement();

                    $eleve = $abo->getEleve();
                    $formule = $abo->getFormule();

                    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
                    $formule = \spamtonprof\stp_api\StpFormule::cast($formule);

                    $nom_formule = $formule->getFormule();
                    $prenom = $eleve->getPrenom();

                    $label = $prenom . ' - ' . $nom_formule;

                    $options[] = array(
                        'label' => $label,
                        'value' => $ref_abo
                    );
                }
            }
        }
    }

    return $options;
}











