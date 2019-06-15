<?php

// toutes ces fonction seront executes par un appel ajax realise dans choisir-prof.js sur la page dont le slug est choisir-prof
add_action('wp_ajax_ajaxAttribuerProf', 'ajaxAttribuerProf');

add_action('wp_ajax_nopriv_ajaxAttribuerProf', 'ajaxAttribuerProf');

/* pour gerer la soumission du formulaire d'essai */
function ajaxAttribuerProf()

{
    header('Content-type: application/json');

    $retour = new \stdClass();

    $retour->error = false;

    $fields = $_POST['fields'];
    $fields = json_decode(stripslashes($fields));

    serializeTemp($fields);

    $ref_abonnement = $fields->ref_abonnement;
    $choix_prof = $fields->choix_prof;
    $action = $fields->action;

    $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

    $constructor = array(
        "construct" => array(
            'ref_eleve',
            'ref_parent'
        )
    );

    $statutAboMg = new \spamtonprof\stp_api\StpStatutAbonnementManager();

    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
    $abonnement = $abonnementMg->get(array(
        'ref_abonnement' => $ref_abonnement
    ), $constructor);

    $constructor = false;

    if ($action == 'attribuer-un-prof') {

        if (! $abonnement->getTest()) {

            $now = $now->add(new \DateInterval("PT30M"));
        }

        $abonnement->setRef_prof($choix_prof);
        $abonnement->setDate_attribution_prof($now);
        $abonnement->setFirst_prof_assigned(false);

        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        $abonnementMg->updateRefProf($abonnement);

        $abonnementMg->updateDateAttributionProf($abonnement);

        $abonnementMg->updateFirstProfAssigned($abonnement);

        $profMg = new \spamtonprof\stp_api\StpProfManager();

        $prof = $profMg->get(array(
            'ref_prof' => $choix_prof
        ));

        $retour->prof = $prof;

        $constructor = array(
            "construct" => array(
                'ref_prof'
            )
        );
    }

    $slack = new \spamtonprof\slack\Slack();
    $slack->sendMessages('log', array(
        $action
    ));

    if ($action == 'refuser-la-demande') {

        // changement du statut -> refuse
        $abonnement->setRef_statut_abonnement($abonnement::REFUSE);
        $abonnementMg->updateRefStatutAbonnement($abonnement);

        /*
         * envoi de l'email - raison du refus:
         * - pas le prof requis ( problème matière )
         * -
         *
         */

        $eleve = $abonnement->getEleve();
        $proche = $abonnement->getProche();

        $eleve->setHasToSend();

        $envoiEleve = $eleve->getHasToSendToEleve();
        $envoiParent = $eleve->getHasToSendToParent();

        $expeMg = new \spamtonprof\stp_api\StpExpeManager();
        $expe = $expeMg->get("info@spamtonprof.com");
        $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
        $smtp = $smtpMg->get(array(
            "ref_smtp_server" => $smtpMg::smtp2Go
        ));

        if ($envoiEleve) {
            $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/refus-essai-eleve.html");
            $body_eleve = str_replace("[[name]]", $eleve->getPrenom(), $body_eleve);
            $smtp->sendEmail("Nous ne pouvons pas accepter ta demande :'( ", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Info SpamTonProf", true);
        }

        if ($envoiParent) {
            $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/refus-essai-parent.html");
            $body_parent = str_replace("[[prenom-parent]]", $proche->getPrenom(), $body_parent);

            $smtp->sendEmail("Nous ne pouvons pas accepter votre demande :'( ", $proche->getEmail(), $body_parent, $expe->getEmail(), "Info SpamTonProf", true);
        }
    }

    if ($action == 'compte-test') {

        $abonnement->setRef_statut_abonnement($abonnement::DESACTIVE);
        $abonnementMg->updateRefStatutAbonnement($abonnement);
    }

    $statutAbo = $statutAboMg->get(array(
        'ref_statut_abonnement' => $abonnement->getRef_statut_abonnement()
    ));

    $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
    $algoliaMg->updateAbonnement($abonnement->getRef_abonnement(), $constructor);

    $retour->statut = $statutAbo;
    $retour->action = $action;
    echo (json_encode($retour));

    die();
}

