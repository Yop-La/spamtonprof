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

$abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
$eleveMg = new \spamtonprof\stp_api\StpEleveManager();
$compteMg = new \spamtonprof\stp_api\StpCompteManager();
$statutEssai = new \spamtonprof\stp_api\StpStatutEssai();
$getresponse = new \GetResponse();


$abonnements = $abonnementMg->getHasNotFirstProfAssignement();

$reponse = [];

foreach ($abonnements as $abonnement) {
    $reponse[] = $abonnement;
    $proche = $abonnement->getProche();
    $eleve = $abonnement->getEleve();
    $formule = $abonnement->getFormule();
    $prof = $abonnement->getProf();

    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

    if ($proche) {
        $proche = \spamtonprof\stp_api\StpProche::cast($proche);
    }

    $formule = \spamtonprof\stp_api\StpFormule::cast($formule);
    $prof = \spamtonprof\stp_api\StpProf::cast($prof);

    $ajoutEleve = $eleve->hasToSendToEleve();
    $ajoutParent = $eleve->hasToSendToParent();

    if ($ajoutEleve) {

        $contact = $getresponse->getContactInList($eleve->getEmail(), "stp_eleve_essai");

        if ($contact) {

            $getresponse->deleteContact($contact->contactId);
        }

        $reponse[] = $getresponse->addEleveInTrialSequence($eleve, $prof, $formule);
    }
    if ($ajoutParent) {

        $listNotFree = $compteMg->getNotFreeParentTrialList($abonnement->getRef_compte());
        $listRetenu = null;

        switch ($listNotFree) {
            case 0:
                $listRetenu = "stp_parent_essai";
                break;
            case 1:
                $listRetenu = "stp_parent_essai_2";
                break;
            case 2:
                $listRetenu = "stp_parent_essai";
                break;
        }

        $contact = $getresponse->getContactInList($proche->getEmail(), $listRetenu);

        if ($contact) {

            $getresponse->deleteContact($contact->contactId);
        }

        if ($listRetenu == "stp_parent_essai") {
            $reponse[] = $getresponse->addParentInTrialSequence1($eleve, $prof, $formule, $proche);
            $eleve->setSeq_email_parent_essai(1);
        } else if ($listRetenu == "stp_parent_essai_2") {
            $reponse[] = $getresponse->addParentInTrialSequence2($eleve, $prof, $formule, $proche);
            $eleve->setSeq_email_parent_essai(2);
        }
        $eleveMg->updateSeqEmailParentEssai($eleve);
    }

    // définir les dates de début et de fin d'essai
    $begin = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

    $abonnement->setDebut_essai($begin->format(PG_DATE_FORMAT));
    $end = $begin->add(new DateInterval('P7D'));
    $abonnement->setFin_essai($end->format(PG_DATE_FORMAT));

    $abonnementMg->updateDebutEssai($abonnement);
    $abonnementMg->updateFinEssai($abonnement);

    // envoyer le mail recap au prof choisi

    $emailRecap = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/mail_recap_prof.html");
    
    // ajout des infos élèves aux mails récap
    $emailRecap = str_replace(array(
        "prenom_eleve",
        "nom_eleve",
        "email_eleve",
        "telephone_eleve",
        "matieres_eleve",
        "niveau_eleve",
        "remarques_eleve"
    ), array(
        $eleve->getPrenom(),
        $eleve->getNom(),
        $eleve->getEmail(),
        $eleve->getTelephone(),
        implode(" ", $formule->getMatieres()),
        $eleve->getNiveau()->getNiveau(),
        $abonnement->getRemarque_inscription()
    ), $emailRecap);

    // ajout des infos du bilan scolaire au mail récap

    $constructor = array(
        "construct" => array(
            'ref_matiere'
        )
    );

    $remarqueMg = new \spamtonprof\stp_api\StpRemarqueInscriptionManager();
    $remarques = $remarqueMg->getAll(array(
        "ref_abonnement" => $abonnement->getRef_abonnement()
    ), $constructor);

    $i = 1;
    foreach ($remarques as $remarque) {

        $matiereIndex = 'matiere' . $i;

        $emailRecap = str_replace(array(
            '[' . $matiereIndex . ']',
            '[' . "remarque_" . $matiereIndex . ']'
        ), array(
            $remarque->getMatiere()->getMatiere_complet(),
            $remarque->getRemarque()
        ), $emailRecap);
        $i ++;
    }

    // ajout des infos parents aux mails récap
    if ($eleve->getParent_required()) {
        $emailRecap = str_replace(array(
            "prenom_parent",
            "nom_parent",
            "email_parent",
            "telephone_parent",
            "statut_parent"
        ), array(
            $proche->getPrenom(),
            $proche->getNom(),
            $proche->getEmail(),
            $proche->getTelephone(),
            $proche->getStatut_proche()
        ), $emailRecap);
    }

    // cacher et relever les bloc matieres et parent
    $dom = new DOMDocument();
    $dom->loadHTML($emailRecap);

    $xpath = new DOMXPath($dom);

    // bloc matieres
    for ($i = 1; $i <= count($remarques); $i ++) {

        $nodes = $xpath->query("/html/body/table/tbody/tr/td/div[2]/div/div/div/div/div/div[" . (2 + $i) . "]/div");

        foreach ($nodes as $node) {

            $node->setAttribute('style', 'line-height: inherit');
        }
    }

    // bloc parent
    if (! $eleve->getParent_required()) {
        $nodes = $xpath->query("/html/body/table/tbody/tr/td/div[2]/div/div/div/div/div/div[8]");

        foreach ($nodes as $node) {
            echo ('dedans');

            $node->setAttribute('style', 'line-height: inherit; display: none !important;');
        }
    }
    $emailRecap = $dom->saveHTML();

    // envoi du mail au prof et à moi

    $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();

    $smtp = $smtpMg->get(array(
        "ref_smtp_server" => $smtpMg::smtp2Go
    ));

    $smtp->sendEmail("Nouvelle inscription à la semaine découverte", $prof->getEmail_stp(), $emailRecap, "alexandre@spamtonprof.com", "Alex de SpamTonProf", true, array(
        "alexandre@spamtonprof.com"
    ));

    $abonnement->setFirst_prof_assigned(true);
    $abonnementMg->updateFirstProfAssigned($abonnement);

    // mise à jour de l'index

    $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

    $constructor = array(
        "construct" => array(
            'ref_prof'
        )
    );

    $algoliaMg->updateAbonnement($abonnement->getRef_abonnement(), $constructor);
}

