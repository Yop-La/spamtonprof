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
 * pour relancer les abonnements en essai ie un abonnement avec un prof attribué depuis au moins 2 jours
 * dont le nombre de message des 7 derniers est inférieur ou égale à 2
 * dont le dernier contact remonte à 2 jours au plus
 * dont la date de relance remonte à 3 jours au plus
 *
 * Pour le moment, la relance est une notification envoyé au prof. Le prof doit se charger lui même de faire la relance.
 *
 */

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

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
        "trial_abo_to_relance",
        "limit" => 10
    ), $constructor);

    foreach ($abos as $abo) {

        $eleve = $abo->getEleve();
        $parent = $abo->getProche();
        $prof = $abo->getProf();

        $now = new \DateTime(null, new \DateTimeZone('Europe/Paris'));

        $dernier_contact = $abo->getDernier_contact();
        if (! is_null($dernier_contact)) {
            $dernier_contact = date_create_from_format(PG_DATETIME_FORMAT, $dernier_contact, new \DateTimeZone('Europe/Paris'));
            $dernier_contact = $dernier_contact->format(FR_DATETIME_FORMAT);
        } else {
            $dernier_contact = " -- impossible à déterminer car aucun message reçu --";
        }

        $name_parent = 'pas de parent :/';
        $email_parent = 'pas de parent :/';
        if ($parent) {
            $parent = \spamtonprof\stp_api\StpProche::cast($parent);
            $name_parent = ucfirst($parent->getPrenom()) . ' ' . ucfirst($parent->getNom());
            $email_parent = $parent->getEmail();
        }
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
        $prof = \spamtonprof\stp_api\StpProf::cast($prof);

        $fin_essai = date_create_from_format(PG_DATE_FORMAT, $abo->getFin_essai(), new \DateTimeZone('Europe/Paris'));
        $disable_date = clone $fin_essai;
        $disable_date = $disable_date->add(new DateInterval('P15D'));

        $date_attribution_prof = date_create_from_format(PG_DATETIME_FORMAT, $abo->getDate_attribution_prof(), new \DateTimeZone('Europe/Paris'));

        $phrase_fin_essai = 'Pour info, l\'essai se termine le : ' . $fin_essai->format(FR_DATETIME_FORMAT);
        if ($now >= $fin_essai) {
            $phrase_fin_essai = 'Pour info, l\'essai est déjà terminé mais on peut prolonger l\'essai';
        }

        // envoyer mail prof
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/relance_prof_essai.html");
        $body_parent = str_replace("[[date_attribution_prof]]", $date_attribution_prof->format(FR_DATETIME_FORMAT), $body_parent);
        $body_parent = str_replace("[[nb_messages]]", $abo->getNb_message() . ' message(s) ', $body_parent);
        $body_parent = str_replace("[[date_dernier_message]]", $dernier_contact, $body_parent);
        $body_parent = str_replace("[[name_parent]]", $name_parent, $body_parent);
        $body_parent = str_replace("[[email_parent]]", $email_parent, $body_parent);
        $body_parent = str_replace("[[name]]", ucfirst($prof->getPrenom()), $body_parent);
        $body_parent = str_replace("[[date_desabonnement]]", $disable_date->format(FR_DATETIME_FORMAT), $body_parent);
        $body_parent = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()) . ' ' . ucfirst($eleve->getNom()), $body_parent);
        $body_parent = str_replace("[[phrase_fin_essai]]", $phrase_fin_essai, $body_parent);

        $smtp->sendEmail(ucfirst($eleve->getPrenom()) . ' ne profite pas de son essai :( ', $prof->getEmail_stp(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true, array(
            'alexandre@spamtonprof.com'
        ), $eleve->getEmail());

        $abo->setRelance_date($now->format(PG_DATETIME_FORMAT));
        $aboMg->updateRelanceDate($abo);

        $msgs = array(
            '---------------',
            'Relance d\'un abo en essai',
            'date_inscription : ' . $abo->getDate_creation(),
            'Date de dernier message: ' . $dernier_contact,
            'ref_abo : ' . $abo->getRef_abonnement(),
            'prof : ' . $prof->getEmail_stp(),
            'email_eleve : ' . $eleve->getEmail()
        );

        if ($parent) {
            $msgs[] = 'email_parent : ' . $parent->getEmail();
        }

        $slack->sendMessages('relance', $msgs);
    }

    $slack->sendMessages('relance', array(
        count($abos) . ' élèves en essai viennent d\'être relancés'
    ));
} while (count($abos) != 0);

$slack->sendMessages('relance', array(
    'Relance des élèves en essai terminé'
));