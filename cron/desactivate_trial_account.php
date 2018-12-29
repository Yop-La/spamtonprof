<?php
/**
 * pour terminer inscription à l'essai après attribution prof manuelle
 * tourne toutes les heures
 * 
 * 
 */
require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-config.php");
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/*
 *  cela désactive les comptes en essai dont l'esssai s'est terminé il y a 15 jours ou plus
 *  il tourne tous les jours
 * 
 */

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
$logMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
$algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
$slack = new \spamtonprof\slack\Slack();

$smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
$smtp = $smtpMg->get(array(
    "ref_smtp_server" => $smtpMg::smtp2Go
));
$expeMg = new \spamtonprof\stp_api\StpExpeManager();
$expe = $expeMg->get("info@spamtonprof.com");

do {

    $constructor = array(
        "construct" => array(
            'ref_prof',
            'ref_eleve',
            'ref_parent'
        )
    );

    $abos = $aboMg->getAll(array(
        "get_trial_account_to_desactivate",
        'limit' => 20
    ), $constructor);

    $slack->sendMessages('log', array(
        'nb abos à désactiver : ' . count($abos)
    ));

    $i = 0;
    foreach ($abos as $abo) {

        $eleve = $abo->getEleve();

        if (! $eleve) {
            prettyPrint($i);
        }
        $i = $i + 1;
        $parent = $abo->getProche();
        $prof = $abo->getProf();

        if ($parent) {
            $parent = \spamtonprof\stp_api\StpProche::cast($parent);
        }
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
        $prof = \spamtonprof\stp_api\StpProf::cast($prof);

        // envoyer mail parent
        if ($eleve->hasToSendToParent()) {
            $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resiliation_essai_parent.html");
            $body_parent = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_parent);
            $body_parent = str_replace("[[name_proche]]", ucfirst($eleve->getPrenom()), $body_parent);
            $body_parent = str_replace("[[name]]", ucfirst($parent->getPrenom()), $body_parent);

            $smtp->sendEmail("L'aventure SpamTonProf se termine", $parent->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true, array(
                $prof->getEmail_stp()
            ));
        }

        // envoyer mail élève
        if ($eleve->hasToSendToEleve()) {
            $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resiliation_essai_eleve.html");
            $body_eleve = str_replace("[[name]]", ucfirst($eleve->getPrenom()), $body_eleve);
            $body_eleve = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_eleve);

            $smtp->sendEmail("L'aventure SpamTonProf se termine", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true, array(
                $prof->getEmail_stp()
            ));
        }

        // changement de statut
        $abo->setRef_statut_abonnement($abo::TERMINE);
        $aboMg->updateRefStatutAbonnement($abo);

        $logMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
            "ref_statut_abo" => $abo->getRef_statut_abonnement(),
            "ref_abonnement" => $abo->getRef_abonnement()
        )));

        // mettre à jour algolia
        $constructor = array(
            "construct" => array(
                'ref_statut_abonnement'
            )
        );
        $algoliaMg->updateAbonnement($abo->getRef_abonnement(), $constructor);

        $msgs = array(
            '---------------',
            'Essai pas concluant',
            'date_inscription : ' . $abo->getDate_creation(),
            'ref_abo : ' . $abo->getRef_abonnement(),
            'prof : ' . $prof->getEmail_stp(),
            'email_eleve : ' . $eleve->getEmail()
        );
        if ($parent) {
            $msgs[] = 'email_parent : ' . $parent->getEmail();
        }

        $slack->sendMessages('fail_conversion', $msgs);
    }
} while (count($abos) != 0);


