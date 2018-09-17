<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans back-office.js/dashboard-prof.js sur la page dont le slug est back-office/dashboard-prof
add_action('wp_ajax_ajaxUpdateEleve', 'ajaxUpdateEleve');

add_action('wp_ajax_nopriv_ajaxUpdateEleve', 'ajaxUpdateEleve');

function ajaxUpdateEleve()
{
    header('Content-type: application/json');
    
    $slack = new \spamtonprof\slack\Slack();
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $email = $_POST["email"];
    $refAbo = $_POST["refAbo"];
    
    // on récupère l'abonnement
    $constructor = array(
        "construct" => array(
            'ref_eleve',
            'ref_parent',
            'ref_statut_abonnement',
            'ref_formule',
            'ref_prof'
        )
    );
    
    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
    
    $abo = $abonnementMg->get(array(
        "ref_abonnement" => $refAbo
    ), $constructor);
    
    $eleve = $abo->getEleve();
    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
    
    $parent = $abo->getProche();
    $parent = \spamtonprof\stp_api\StpProche::cast($parent);
    
    $formule = $abo->getFormule();
    $prof = $abo->getProf();
    
    if ($eleve->getEmail() == $email) {
        
        $retour->message = utf8_encode("Aucune mise à jour faite : l'email est identique");
        echo (json_encode($retour));
        die();
    }
    
    // same email ?
    $sameEmail = false;
    if (strtolower($parent->getEmail()) == strtolower($email)) {
        $sameEmail = true;
    }
    
    // maj getresponse - remove list essai + ajout liste essai si essai
    $retour->message = utf8_encode("L'email (" . $email . ") a bien été mise à jour");
    if ($abo->getFirst_prof_assigned() && $abo->getRef_statut_abonnement() == $abo::ESSAI && ! $sameEmail) {
        
        $gr = new \GetResponse();
        $contact = $gr->getContactInList($eleve->getEmail(), "stp_eleve_essai");
        
        $dayOfCycle = 0;
        if ($contact) {
            $dayOfCycle = $contact->dayOfCycle;
            $gr->deleteContact($contact->contactId);
        }
        $eleve->setEmail($email);
        $gr->addEleveInTrialSequence($eleve, $prof, $formule, $dayOfCycle);
    }
    
    // mise à jour de l'email dans la base
    $eleve->setEmail($email);
    $eleve->setSame_email($sameEmail);
    $eleveMg->updateEmail($eleve);
    
    // update index
    $algolia = new \spamtonprof\stp_api\AlgoliaManager();
    $algolia->updateAbonnement($refAbo, $constructor);
    
    echo (json_encode($retour));
    
    die();
}

