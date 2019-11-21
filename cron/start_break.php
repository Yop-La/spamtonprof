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

// define('PROBLEME_CLIENT', true);

$stripe = new \spamtonprof\stp_api\StripeManager();

$interruptionMg = new \spamtonprof\stp_api\StpInterruptionManager();
$interruptions = $interruptionMg->getAll(array(
    'key' => 'to_start'
));

$algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

$slack = new \spamtonprof\slack\Slack();

$slack->sendMessages('interruption', array(
    'Running CRON mise en place interruption'
));

foreach ($interruptions as $interruption) {

    $slack->sendMessages('interruption', array(
        "---------------------",
        "Mise en place interruption n°" . $interruption->getRef_interruption()
    ));

    $msgs = array();

    $endDay = \DateTime::createFromFormat(PG_DATE_FORMAT, $interruption->getFin());
    $endDay = $endDay->format(FR_DATE_FORMAT);

    $abo = $aboMg->get_full_abo($interruption->getRef_abonnement());

    // ------- mise en place interruption slack -------

    $stripe->addTrial($abo->getSubs_Id(), $interruption->getFin());

    // -------------------- notis emails ------------------

    $proche = \spamtonprof\stp_api\StpProche::cast($abo->getProche());

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

        $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/debut_interruption_eleve.html");

        $body_eleve = str_replace("[nom-formule]", $formule->getFormule(), $body_eleve);
        $body_eleve = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_eleve);
        $body_eleve = str_replace("[date-fin]", $endDay->format(FR_DATE_FORMAT), $body_eleve);

        $smtp->sendEmail("Interruption en place", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true, $ccs);

        $send_to_prof = false;
    }

    if ($envoiParent) {
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/debut_interruption_parent.html");
        $body_parent = str_replace("[prenom-parent]", $proche->getPrenom(), $body_parent);
        $body_parent = str_replace("[nom-formule]", $formule->getFormule(), $body_parent);
        $body_parent = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_parent);
        $body_parent = str_replace("[date-fin]", $endDay, $body_parent);

        if (! $send_to_prof) {
            $ccs = false;
        }

        $smtp->sendEmail("Interruption en place", $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true, $ccs);
    }

    // mise à jour alogia et table abonnement
    $abo->setInterruption(true);
    $aboMg->updateInterruption($abo);

    $abo = $aboMg->toAlgoliaSupport($interruption->getRef_abonnement());
    $algoliaMg->updateSupport($abo);

    // changement du statut
    $interruption->setStatut($interruptionMg::stopping);
    $interruptionMg->update_statut($interruption);

    $slack->sendMessages('interruption', $msgs);
}

$slack->sendMessages('interruption', array(
    'Fin CRON mise en place interruption'
));