<?php
/*
 * cron de mise en place des interruptions.
 * Tourne après minuit - au début de la journée
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

define('PROBLEME_CLIENT', true);

$interruptionMg = new \spamtonprof\stp_api\StpInterruptionManager();
$interruptions = $interruptionMg->getAll(array(
    'key' => 'to_stop'
));

$algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$slack = new \spamtonprof\slack\Slack();

$slack->sendMessages('interruption', array(
    'Running CRON arrêt interruption'
));

foreach ($interruptions as $interruption) {

    $slack->sendMessages('interruption', array(
        "---------------------",
        "Fin de l'interruption n°" . $interruption->getRef_interruption()
    ));

    $msgs = array();

    $endDay = \DateTime::createFromFormat(PG_DATE_FORMAT, $interruption->getFin());
    $endDay = $endDay->format(FR_DATE_FORMAT);

    $abo = $aboMg->get_full_abo($interruption->getRef_abonnement());

    // -------------------- notis emails ------------------

    $proche=$abo->getProche();
    if ($proche) {
        $proche = \spamtonprof\stp_api\StpProche::cast($proche);
    }

    $prof = \spamtonprof\stp_api\StpProf::cast($abo->getProf());

    $formule = \spamtonprof\stp_api\StpFormule::cast($abo->getFormule());

    $eleve = \spamtonprof\stp_api\StpEleve::cast($abo->getEleve());
    $eleve->setHasToSend();

    if ($proche) {
        $msgs[] = 'Proche:' . $proche->getEmail();
    }
    $msgs[] = 'Eleve: ' . $eleve->getEmail();
    $msgs[] = 'Ref abonnement: ' . $abo->getRef_abonnement();
    $msgs[] = 'Date de fin: ' . $endDay;

    $envoiEleve = $eleve->getHasToSendToEleve();
    $envoiParent = $eleve->getHasToSendToParent();

    $expeMg = new \spamtonprof\stp_api\StpExpeManager();
    $expe = $expeMg->get("info@spamtonprof.com");
    $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
    $smtp = $smtpMg->get(array(
        "ref_smtp_server" => $smtpMg::smtp2Go
    ));

    $send_to_prof = true;
    $ccs = array(
        'alexandre@spamtonprof.com',
        $prof->getEmail_stp()
    );

    if ($envoiEleve) {

        $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/fin_interruption_eleve.html");

        $body_eleve = str_replace("[nom-formule]", $formule->getFormule(), $body_eleve);
        $body_eleve = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_eleve);

        $smtp->sendEmail("C'est la reprise !", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true, $ccs);

        $send_to_prof = false;
    }

    if ($envoiParent) {
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/fin_interruption_eleve.html");
        $body_parent = str_replace("[prenom-parent]", $proche->getPrenom(), $body_parent);
        $body_parent = str_replace("[nom-formule]", $formule->getFormule(), $body_parent);
        $body_parent = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_parent);

        if (! $send_to_prof) {
            $ccs = false;
        }

        $smtp->sendEmail("C'est la reprise !", $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true, $ccs);
    }

    // mise à jour alogia et table abonnement
    $abo->setInterruption(false);
    $aboMg->updateInterruption($abo);

    $abo = $aboMg->toAlgoliaSupport($interruption->getRef_abonnement());
    $algoliaMg->updateSupport($abo);

    // changement du statut
    $interruption->setStatut($interruptionMg::done);
    $interruptionMg->update_statut($interruption);

    $slack->sendMessages('interruption', $msgs);
}

$slack->sendMessages('interruption', array(
    'Fin CRON arrêt interruption'
));