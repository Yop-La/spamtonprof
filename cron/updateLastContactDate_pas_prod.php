<?php
use spamtonprof\slack\Slack;

/**
 * il ne traque les emails des élève de des étudiants ( pas des parents )
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

// script de mise à jour de la date de dernir contact

// on parcoure chaque abonnement actif et en essai dont l'inscription remontent à au moins 15 jours
// pour chaque abo, on récupère l'email de l'élève et l'adresse mail du prof

// on recherche le dernier mail dans la boite du prof

$aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
$slack = new \spamtonprof\slack\Slack();

$profMg = new \spamtonprof\stp_api\StpProfManager();
$gmailAccountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
$algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

$profs = $profMg->getAll(array(
    "inbox_ready" => true
));

foreach ($profs as $prof) {

    $gmailAccount = $gmailAccountMg->get($prof->getRef_gmail_account());
    $email_prof = $gmailAccount->getEmail();

    $slack->sendMessages('log', array(
        'mise à jour des dates de dernier contact pour: ' . $email_prof
    ));

    $constructor = array(
        "construct" => array(
            'ref_eleve'
        )
    );

    $gmail = new \spamtonprof\googleMg\GoogleManager($email_prof);
    $offset = 0;

    // $offset = unserializeTemp("/tempo/offset_dernier_contact");

    // if (! $offset) {
    // $offset = 0;
    // serializeTemp($offset, "/tempo/offset_dernier_contact");
    // }

    do {

        $slack->sendMessages('log', array_merge(array(
            '-------- params getAll',
            'offset : ' . $offset
        )));

        $abos = $aboMg->getAll(array(
            'abo_vivant',
            'offset' => $offset,
            'ref_prof' => $prof->getRef_prof(),
            'limit' => 20
        ), $constructor);

        foreach ($abos as $abo) {

            $eleve = $abo->getEleve();
            if ($eleve) {

                $messages = $gmail->listMessages('from:' . $eleve->getEmail(), 1, 1);
                         
                if (count($messages) == 0) {
                    continue;
                }
                $msg = $messages[0];
                $msg = $gmail->getMessage($msg->id, [
                    'format' => 'minimal'
                ]);

                // problème : le mail remonté n'est pas le dernier mais le premier du dernier thread
                // solution : remonter le dernier thread grâce à Threads.list, récupérer ce thread avec Threads.get
                // Ce thread contient une liste de message. Il suffit alors de récupérer le message de ce thread avec le plus grand internalDate

                $timeStamp = $msg->internalDate / 1000;
                $dateReception = new DateTime();
                $dateReception->setTimestamp($timeStamp);
                $dateReception->setTimezone(new \DateTimeZone('Europe/Paris'));
                $dateReception = $dateReception->format(PG_DATETIME_FORMAT);

                $abo->setDernier_contact($dateReception);
                $aboMg->updateDernierContact($abo);

                $algoliaMg->updateAbonnement($abo->getRef_abonnement());

                $slack->sendMessages('log', array(
                    'date reception : ' . $dateReception,
                    'offset : ' . $offset,
                    'abo : ' . $abo->getRef_abonnement(),
                    'email_eleve : ' . $eleve->getEmail()
                ));
            }
        }
        $offset = $offset + 20;
        // serializeTemp($offset, "/tempo/offset_dernier_contact");

    } while (count($abos) != 0);


    $offset = 0;
    // serializeTemp($offset, "/tempo/offset_dernier_contact");

    $slack->sendMessages('log', array(
        'mise à jour des dates de dernier contact faites pour: ' . $prof->getPrenom()
    ));
}

