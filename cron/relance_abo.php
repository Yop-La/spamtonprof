<?php
/**
 * pour terminer inscription � l'essai apr�s attribution prof manuelle
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
 * pour relancer les abonnements actifs c'est � un dire un abonnement payant dont l'�l�ve n'a pas envoy� de messages
 * depuis 10 jours. La relance se fait ensuite tous les 5 jours.
 * Pour le moment, la relance est une notification envoy� au prof. Le prof doit se charger lui m�me de faire la relance.
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
        'nb_inactif_day' => 10,
        'ref_statut_abonnement' => 1,
        'days_since_relance' => 5,
        'limit' => 10
    ), $constructor);

    foreach ($abos as $abo) {

        $eleve = $abo->getEleve();
        $parent = $abo->getProche();
        $prof = $abo->getProf();

        $now = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
        $nb_inactif_days = " -- impossible � d�terminer car aucun message re�u (l'�l�ve doit utiliser une autre adresse email) --";
        $dernier_contact = $abo->getDernier_contact();
        if (! is_null($dernier_contact)) {
            $dernier_contact = date_create_from_format(PG_DATETIME_FORMAT, $dernier_contact, new \DateTimeZone('Europe/Paris'));

            $nb_inactif_days = $now->diff($dernier_contact);
            $nb_inactif_days = $nb_inactif_days->format('%a');
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

        // envoyer mail prof
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/relance_prof_actif.html");
        $body_parent = str_replace("[[nb_jour]]", $nb_inactif_days . ' jour(s) ', $body_parent);
        $body_parent = str_replace("[[name_parent]]", $name_parent, $body_parent);
        $body_parent = str_replace("[[email_parent]]", $email_parent, $body_parent);
        $body_parent = str_replace("[[name]]", ucfirst($prof->getPrenom()), $body_parent);
        $body_parent = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()) . ' ' . ucfirst($eleve->getNom()), $body_parent);

        $smtp->sendEmail(ucfirst($eleve->getPrenom()) . " t'a oubli�(e)", $prof->getEmail_stp(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true, array(
            'alexandre@spamtonprof.com'
        ), $eleve->getEmail());

        $abo->setRelance_date($now->format(PG_DATETIME_FORMAT));
        $aboMg->updateRelanceDate($abo);

        $msgs = array(
            '---------------',
            'Relance d\'un abo actif',
            'date_inscription : ' . $abo->getDate_creation(),
            'Nombre de jours d\'inactivit�: ' . $nb_inactif_days,
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
        count($abos) . ' �l�ves abonn�s viennent d\'�tre relanc�s'
    ));
} while (count($abos) != 0);

$slack->sendMessages('relance', array(
    'Relance des �l�ves abonn�s termin�'
));

