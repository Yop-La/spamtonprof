<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans dashboard-eleve.js sur la page dont le slug est dashboard-eleve
add_action('wp_ajax_ajaxCreateSubscription', 'ajaxCreateSubscription');

add_action('wp_ajax_nopriv_ajaxCreateSubscription', 'ajaxCreateSubscription');

add_action('wp_ajax_ajaxStopSubscription', 'ajaxStopSubscription');

add_action('wp_ajax_nopriv_ajaxStopSubscription', 'ajaxStopSubscription');

function ajaxStopSubscription()
{
    header('Content-type: application/json');
    
    $slack = new \spamtonprof\slack\Slack();
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $refAbonnement = $_POST["ref_abonnement"];
    $testMode = $_POST["testMode"];
    
    // on récupère l'abonnement
    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
    $constructor = array(
        "construct" => array(
            'ref_prof',
            'ref_eleve',
            'ref_parent',
            'ref_formule'
        )
    );
    
    $abonnement = $abonnementMg->get(array(
        "ref_abonnement" => $refAbonnement
    ), $constructor);
    
    $eleve = $abonnement->getEleve();
    $proche = $abonnement->getProche();
    $prof = $abonnement->getProf();
    $formule = $abonnement->getFormule();
    
    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
    $prof = \spamtonprof\stp_api\StpProf::cast($prof);
    
    $proche = \spamtonprof\stp_api\StpProche::cast($proche);
    if ($proche) {
        $proche = \spamtonprof\stp_api\StpProche::cast($proche);
    }
    $formule = \spamtonprof\stp_api\StpFormule::cast($formule);
    
    // résilier abonnement stripe
    $stripeMg = new \spamtonprof\stp_api\StripeManager($testMode);
    $stripeMg->stopSubscription($abonnement->getSubs_Id());
    
    // statut abonnement de actif à pas actif
    $abonnement->setRef_statut_abonnement($abonnement::TERMINE);
    $abonnementMg->updateRefStatutAbonnement($abonnement);
    
    $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
    $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
        "ref_abonnement" => $abonnement->getRef_abonnement(),
        "ref_statut_abo" => $abonnement->getRef_statut_abonnement()
    )));
    
    // envoyer mails de résiliation à famille + prof (pour demander témoignage)
    
    $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
    $smtp = $smtpMg->get(array(
        "ref_smtp_server" => $smtpMg::smtp2Go
    ));
    $expeMg = new \spamtonprof\stp_api\StpExpeManager();
    $expe = $expeMg->get("info@spamtonprof.com");
    
    if ($eleve->hasToSendToParent()) {
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resiliation_abonnement_parent.html");
        $body_parent = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_parent);
        $body_parent = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()), $body_parent);
        $body_parent = str_replace("[[name]]", ucfirst($proche->getPrenom()), $body_parent);
        $body_parent = str_replace("[[formule]]", $formule->getFormule(), $body_parent);
        
        $smtp->sendEmail("C'est fait : l'abonnement de " . $eleve->getPrenom() . " est résilié.", $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }
    
    if ($eleve->hasToSendToEleve()) {
        $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resiliation_abonnement_eleve.html");
        $body_eleve = str_replace("[[name]]", ucfirst($eleve->getPrenom()), $body_eleve);
        $body_eleve = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_eleve);
        $body_eleve = str_replace("[[formule]]", $formule->getFormule(), $body_eleve);
        $smtp->sendEmail("C'est fait : ton abonnement est résilié.", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }
    
    // envoi prof
    $body_prof = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resilier_prof.html");
    $body_prof = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()), $body_prof);
    $body_prof = str_replace("[[formule]]", $formule->getFormule(), $body_prof);
    $body_prof = str_replace("[[name]]", ucfirst($prof->getPrenom()), $body_prof);
    $body_prof = str_replace("[[adresse_eleve]]", $eleve->getEmail(), $body_prof);
    
    if ($proche) {
        $body_prof = str_replace("[[adresse_parent]]", $proche->getEmail(), $body_prof);
    }
    
    $smtp->sendEmail("Tu peux récupérer un témoignage ! ", $prof->getEmail_stp(), $body_prof, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    
    echo (json_encode($retour));
    
    die();
}

function ajaxCreateSubscription()
{
    header('Content-type: application/json');
    
    $slack = new \spamtonprof\slack\Slack();
    
    $retour = new \stdClass();
    
    $retour->error = false;
    
    $refAbonnement = $_POST["ref_abonnement"];
    $source = $_POST["source"];
    $testMode = $_POST["testMode"];
    
    // on récupère l'abonnement
    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
    $constructor = array(
        "construct" => array(
            'ref_prof',
            'ref_eleve',
            'ref_parent',
            'ref_formule',
            'ref_plan'
        )
    );
    
    $abonnement = $abonnementMg->get(array(
        "ref_abonnement" => $refAbonnement
    ), $constructor);
    
    $eleve = $abonnement->getEleve();
    $proche = $abonnement->getProche();
    $prof = $abonnement->getProf();
    $plan = $abonnement->getPlan();
    $formule = $abonnement->getFormule();
    
    $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
    $prof = \spamtonprof\stp_api\StpProf::cast($prof);
    $plan = \spamtonprof\stp_api\StpPlan::cast($plan);
    $formule = \spamtonprof\stp_api\StpFormule::cast($formule);
    
    // détermination de l'email client
    $emailClient = "alexandre@spamtonprof.com";
    if ($proche) {
        $proche = \spamtonprof\stp_api\StpProche::cast($proche);
        $emailClient = $proche->getEmail();
    } else {
        $emailClient = $eleve->getEmail();
    }
    
    // on ajoute l'abonnement à stripe pour débiter le client de manière récurrente
    $stripeMg = new \spamtonprof\stp_api\StripeManager($testMode);
    
    $subscriptionCreated = false;
    if ($testMode == "true") {
        
        $subsId = $stripeMg->addConnectSubscription($emailClient, $source, $abonnement->getRef_compte(), $plan->getRef_plan_stripe_test(), $prof->getStripe_id_test(), $abonnement->getRef_abonnement());
    } else {
        
        $subsId = $stripeMg->addConnectSubscription($emailClient, $source, $abonnement->getRef_compte(), $plan->getRef_plan_stripe(), $prof->getStripe_id(), $abonnement->getRef_abonnement());
    }
    
    if (! $subsId) {
        
        $retour->error = true;
        $retour->message = utf8_encode("Impossible de débiter votre moyen de paiement");
        echo (json_encode($retour));
        die();
    } else {
        
        $abonnement->setSubs_Id($subsId);
        $abonnementMg->updateSubsId($abonnement);
        
        $abonnement->setRef_statut_abonnement(\spamtonprof\stp_api\StpStatutAbonnementManager::ACTIF);
        $abonnementMg->updateRefStatutAbonnement($abonnement);
        
        $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
        $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
            "ref_abonnement" => $abonnement->getRef_abonnement(),
            "ref_statut_abo" => $abonnement->getRef_statut_abonnement()
        )));
        
        $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
        $smtp = $smtpMg->get(array(
            "ref_smtp_server" => $smtpMg::smtp2Go
        ));
        $expeMg = new \spamtonprof\stp_api\StpExpeManager();
        $expe = $expeMg->get("info@spamtonprof.com");
        
        if ($eleve->hasToSendToParent()) {
            $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/abonnement_parent.html");
            $body_parent = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_parent);
            $body_parent = str_replace("[[name_proche]]", ucfirst($eleve->getPrenom()), $body_parent);
            $body_parent = str_replace("[[name]]", ucfirst($proche->getPrenom()), $body_parent);
            
            $smtp->sendEmail("Félicitations, " . ucfirst($eleve->getPrenom()) . " a compris notre philosophie", $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }
        
        if ($eleve->hasToSendToEleve()) {
            $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/abonnement_eleve.html");
            $body_eleve = str_replace("[[name]]", ucfirst($eleve->getPrenom()), $body_eleve);
            $body_eleve = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_eleve);
            $smtp->sendEmail("Félicitations, tu a compris notre philosophie", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }
        
        // envoi prof
        $body_prof = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/abonnement_prof.html");
        $body_prof = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()), $body_prof);
        $body_prof = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_prof);
        $body_prof = str_replace("[[formule]]", $formule->getFormule(), $body_prof);
        $body_prof = str_replace("[[tarif]]", $plan->getTarif(), $body_prof);
        $smtp->sendEmail("Bravo, une semaine d'essai concluante pour " . $eleve->getPrenom() . "! ", $prof->getEmail_stp(), $body_prof, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
        
        $constructor = array(
            "construct" => array(
                'ref_statut_abonnement'
            )
        );
        
        $algoliaMg->updateAbonnement($abonnement->getRef_abonnement(), $constructor);
        
    }
    
    echo (json_encode($retour));
    
    die();
}
