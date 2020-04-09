<?php
use spamtonprof\slack\Slack;

/**
 * il ne traque les emails des �l�ve de des �tudiants ( pas des parents )
 *
 * ce script sert :
 * - à stocker dans mail eleve - les messages des élèves
 * - à attribuer des libellées aux emails
 * - il tourne tous les 5 minutes
 * - à mettre à jour la date de dernier contact
 *
 * en prod
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

$slack = new \spamtonprof\slack\Slack();

$profMg = new \spamtonprof\stp_api\StpProfManager();
$prof = $profMg->getNextInboxToProcess();

echo ('<br>');
echo ($prof->getPrenom());
echo ('<br>');

$now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

$prof->setProcessing_date($now->format(PG_DATETIME_FORMAT));
$profMg->updateProcessingDate($prof);

$gmailAccountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
$gmailAccount = $gmailAccountMg->get($prof->getRef_gmail_account());

$gmailManager = new spamtonprof\googleMg\GoogleManager($gmailAccount->getEmail());

$slack->sendMessages("message_eleve", array(
    " ----- ",
    "Lecture de : " . $gmailAccount->getEmail()
));

$MessEleveMg = new \spamtonprof\stp_api\StpMessageEleveManager();

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
$eleveMg = new \spamtonprof\stp_api\StpEleveManager();

// gestion last history id

$historyId = $gmailAccount->getLast_history_id();
$ret = $gmailManager->getLastMessage($historyId);

$messages = $ret['msgs'];
$historyId = $ret['historyId'];

foreach ($messages as $message) {

    if (! $message) {
        continue;
    }

    $gmailId = $message->id;

    $from = extractFirstMail($gmailManager->getHeader($message, "From"));

    $timeStamp = $message->internalDate / 1000;

    $dateReception = new DateTime();
    $dateReception->setTimestamp($timeStamp);

    $dateReception->setTimezone(new \DateTimeZone('Europe/Paris'));

    $eleve = false;
    $eleve = $eleveMg->get(array(
        "email" => $from
    ));

    echo ("mail : " . $from . " -- new date reception : " . $dateReception->format(PG_DATETIME_FORMAT) . " -- message id : " . $gmailId . "<br><br>");

    if ($eleve) {

        $constructor = array(
            "construct" => array(
                'ref_eleve',
                'ref_statut_abonnement'
            ),
            "ref_eleve" => array(
                "construct" => array(
                    'ref_niveau'
                )
            )
        );

        $abos = $aboMg->getAll(array(
            "ref_eleve" => $eleve->getRef_eleve(),
            "ref_prof" => $prof->getRef_prof()
        ), $constructor);

        $nbAbos = count($abos);

        $labelsNameToAdd = [];
        switch ($nbAbos) {
            case 0:
                $labelsNameToAdd[] = 'error_pas_formule';
                break;
            case 2:
                $labelsNameToAdd[] = 'error_double_formule';
                break;
            case 1:

                $abo = $abos[0];

                // sauvegarder le message
                $MessEleveMg->add(new \spamtonprof\stp_api\StpMessageEleve(array(
                    'ref_abonnement' => $abo->getRef_abonnement(),
                    'date_message' => $dateReception->format(PG_DATETIME_FORMAT),
                    'ref_gmail' => $gmailId,
                    'mail_expe' => $from,
                    'ref_prof' => $prof->getRef_prof(),
                    'ref_gmail_account' => $gmailAccount->getRef_gmail_account()
                )));

                // attribuer les libellées

                $eleve = $abo->getEleve();
                $statut = $abo->getStatut();

                $niveau = \spamtonprof\stp_api\StpNiveauManager::cast($eleve->getNiveau());
                $statut = \spamtonprof\stp_api\StpStatutAbonnement::cast($statut);

                $slack->sendMessages("message_eleve", array(
                    " ---- ",
                    "Nouveau message de : " . $eleve->getPrenom(),
                    $niveau->getNiveau(),
                    $gmailId,
                    $dateReception->format(PG_DATETIME_FORMAT),
                    "Avec " . $prof->getPrenom()
                ));

                if ($abo->isTrialOver() && $abo->getRef_statut_abonnement() == $abo::ESSAI) {
                    $labelsNameToAdd[] = 'test-over';
                }

                $labelsNameToAdd[] = $niveau->getSigle();
                $labelsNameToAdd[] = $statut->getStatut_abonnement();

                // mettre à jour la date de dernier contact
                $abo->setDernier_contact($dateReception->format(PG_DATETIME_FORMAT));
                $aboMg->updateDernierContact($abo);

                $abo->setNb_relance_since_no_news(0);
                $aboMg->updateNbRelanceSinceNoNews($abo);

                $abo->setTo_relaunch(false);
                $aboMg->updateToRelaunch($abo);
                
                // mise à jour de l'index
                $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
                $algoliaMg->updateAbonnement($abo->getRef_abonnement(), false);

                break;
            default:
                $slack->sendMessages("message_eleve", array(
                    "Nb d'abonnements incohérent au moment du tracking des élèves. Voir ProcessMessageEleve.php"
                ));
                continue;
        }

        // attribuer les libellées
        $labelsToAdd = $gmailManager->getCustomLabelsToAdd($labelsNameToAdd);

        $gmailManager->modifyMessage($gmailId, $labelsToAdd, array());
    }
}

$gmailAccount->setLast_history_id($historyId);
$gmailAccountMg->updateHistoryId($gmailAccount);

exit(0);

