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

use Sunra\PhpSimple\HtmlDomParser;

$abonnementMg = new \spamtonprof\stp_api\stpAbonnementManager();
$eleveMg = new \spamtonprof\stp_api\stpEleveManager();
$compteMg = new \spamtonprof\stp_api\stpCompteManager();
$statutEssai = new \spamtonprof\stp_api\stpStatutEssai();
$getresponse = new \GetResponse();

$abonnements = $abonnementMg->getHasNotFirstProfAssignement();

$reponse = [];

foreach ($abonnements as $abonnement) {
    $reponse[] = $abonnement;
    $proche = $abonnement->getProche();
    $eleve = $abonnement->getEleve();
    $formule = $abonnement->getFormule();
    $prof = $abonnement->getProf();
    
    $eleve = \spamtonprof\stp_api\stpEleve::cast($eleve);
    
    if ($proche) {
        $proche = \spamtonprof\stp_api\stpProche::cast($proche);
    }
    
    $formule = \spamtonprof\stp_api\StpFormule::cast($formule);
    $prof = \spamtonprof\stp_api\stpProf::cast($prof);
    
    
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
    
    $emailRecap = file_get_contents(dirname(dirname(__FILE__)) . "/emails/mail_recap_prof.html");
    
    $html = HtmlDomParser::str_get_html($emailRecap);
    
    foreach ($formule->getMatieres() as $matiere) {
        
        switch ($matiere) {
            case "francais":
                $elems = $html->find('.francais');
                $elems[0]->{'style'} = "line-height: inherit;";
                break;
            case "maths":
                $elems = $html->find('.maths');
                $elems[0]->{'style'} = "line-height: inherit;";
                break;
            case "physique":
                $elems = $html->find('.physique');
                $elems[0]->{'style'} = "line-height: inherit;";
                break;
        }
    }
    
    $emailRecap = $html;
    
    // ajout des infos élèves aux mails récap
    $emailRecap = str_replace(array(
        "prenom_eleve",
        "nom_eleve",
        "email_eleve",
        "telephone_eleve",
        "matieres_eleve",
        "classe_eleve",
        "profil_eleve",
        "remarques_eleve"
    ), array(
        $eleve->getPrenom(),
        $eleve->getNom(),
        $eleve->getEmail(),
        $eleve->getTelephone(),
        implode(" ", $formule->getMatieres()),
        $eleve->getClasse()->getNom_Complet(),
        $eleve->getProfil()->getProfil(),
        $abonnement->getRemarque_inscription()
    ), $emailRecap);
    
    // ajout des infos du bilan scolaire au mail récap
    $remarqueMg = new \spamtonprof\stp_api\stpRemarqueInscriptionManager();
    $remarques = $remarqueMg->getAll(array(
        "ref_abonnement" => $abonnement->getRef_abonnement()
    ));
    
    foreach ($remarques as $remarque) {
        
        switch ($remarque->getRef_matiere()) {
            case \spamtonprof\stp_api\stpMatiere::FRANCAIS:
                
                $emailRecap = str_replace(array(
                    "diff_francais",
                    "note_francais",
                    "chapitres_francais"
                ), array(
                    $remarque->getDifficulte(),
                    $remarque->getNote(),
                    $remarque->getChapitre()
                ), $emailRecap);
                
                break;
            case \spamtonprof\stp_api\stpMatiere::PHYSIQUE:
                
                $emailRecap = str_replace(array(
                    "diff_physique",
                    "note_physique",
                    "chapitres_physique"
                ), array(
                    $remarque->getDifficulte(),
                    $remarque->getNote(),
                    $remarque->getChapitre()
                ), $emailRecap);
                break;
            case \spamtonprof\stp_api\stpMatiere::MATHS:
                
                $emailRecap = str_replace(array(
                    "diff_maths",
                    "note_maths",
                    "chapitres_maths"
                ), array(
                    $remarque->getDifficulte(),
                    $remarque->getNote(),
                    $remarque->getChapitre()
                ), $emailRecap);
                break;
        }
    }
    
    // ajout des infos parents aux mails récap
    if ($eleve->getRef_profil() == \spamtonprof\stp_api\stpProfil::ETUDIANT) {
        $elems = $html->find('.parent');
        $elems[0]->outertext = '';
    } else {
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
}

