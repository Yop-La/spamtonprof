<?php
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

// en prod. Toutes les heures
/* cron d'envoi des emails de relance */

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$slack = new \spamtonprof\slack\Slack();

$constructor = array(
    "construct" => array(
        'ref_prof',
        'ref_eleve',
        'ref_parent'
    )
);

$abos = $aboMg->getAll(array(
    "key" => "abo_to_relaunch"
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
        $dernier_contact = "Aucun message reçu pour le moment :/";
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

    $phrase_fin_essai = 'l\'essai se termine le : ' . $fin_essai->format(FR_DATETIME_FORMAT);
    if ($now >= $fin_essai) {
        $phrase_fin_essai = 'l\'essai est déjà terminé mais on peut le prolonger si il faut';
    }

    /*
     *
     * envoi du mail
     *
     */

    if ($abo->getRef_statut_abonnement() == $abo::ESSAI) {

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

        $to = $prof->getEmail_stp();
        // $to = 'alexandre@spamtonprof.com';

        $email->addTo($to, $prof->getPrenom(), [

            "prof_name" => ucfirst($prof->getPrenom()),
            "eleve_name" => ucfirst($eleve->getPrenom()) . ' ' . ucfirst($eleve->getNom()),
            "date_attribution_prof" => $date_attribution_prof->format(FR_DATETIME_FORMAT),
            "date_dernier_message" => $dernier_contact,
            "phrase_essai" => $phrase_fin_essai,
            "date_desactivation" => $disable_date->format(FR_DATETIME_FORMAT),
            "prenom_parent" => $name_parent,
            "email_parent" => $email_parent,
            "eleve_firstname" => ucfirst($eleve->getPrenom())
        ], 0);

        $email->setReplyTo($eleve->getEmail());

        $email->addCc('alexandre@spamtonprof.com');

        $email->setTemplateId("d-b65aa9d6d4a94aeca3a24ea250f90506");
        $sendgrid = new \SendGrid(SEND_GRID_API_KEY);
        try {
            $response = $sendgrid->send($email);

            echo ($response->body());
        } catch (\Exception $e) {

            $slack->sendMessages('relance', 'Erreur d\envoi du mail de relance', 'Caught exception: ' . $e->getMessage());
        }

        $nbRelance = $abo->getNb_relance_since_no_news();
        $abo->setNb_relance_since_no_news(0);
        if ($nbRelance) {

            $abo->setNb_relance_since_no_news($nbRelance + 1);
        }
    }

    if ($abo->getRef_statut_abonnement() == $abo::ACTIF) {

        $to = $prof->getEmail_stp();
        // $to = 'alexandre@spamtonprof.com';

        $email->addTo($to, $prof->getPrenom(), [

            "prof_name" => ucfirst($prof->getPrenom()),
            "eleve_name" => ucfirst($eleve->getPrenom()) . ' ' . ucfirst($eleve->getNom()),
            "date_attribution_prof" => $date_attribution_prof->format(FR_DATETIME_FORMAT),
            "date_dernier_message" => $dernier_contact,
            "prenom_parent" => $name_parent,
            "email_parent" => $email_parent,
            "eleve_firstname" => ucfirst($eleve->getPrenom())
        ], 0);

        $email->setReplyTo($eleve->getEmail());

        $email->addCc('alexandre@spamtonprof.com');

        $email->setTemplateId("d-7010626a9fdf47199bf028c5549f5bad");
        $sendgrid = new \SendGrid(SEND_GRID_API_KEY);
        try {
            $response = $sendgrid->send($email);

            echo ($response->body());
        } catch (\Exception $e) {

            $slack->sendMessages('relance', 'Erreur d\envoi du mail de relance', 'Caught exception: ' . $e->getMessage());
        }

        $nbRelance = $abo->getNb_relance_since_no_news();
        $abo->setNb_relance_since_no_news(0);
        if ($nbRelance) {

            $abo->setNb_relance_since_no_news($nbRelance + 1);
        }
    }

    /*
     *
     * mise à jour en base
     *
     */

    $abo->setRelance_date($now->format(PG_DATETIME_FORMAT));
    $aboMg->updateRelanceDate($abo);

    $abo->setTo_relaunch(false);
    $aboMg->updateToRelaunch($abo);

    $aboMg->updateNbRelanceSinceNoNews($abo);

    /*
     *
     * log slack
     *
     */

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

$slack->sendMessages('relance', array(
    'Relance des élèves en essai terminés'
));