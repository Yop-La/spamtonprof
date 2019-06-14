<?php

// toutes ces fonction seront executes par un appel ajax realise dans paiement.js sur la page dont le slug est paiement
add_action('wp_ajax_paiement_inscription', 'paiement_inscription');

add_action('wp_ajax_nopriv_paiement_inscription', 'paiement_inscription');

// uniquement utilisé pour le stage d'été pour le moment
function paiement_inscription()
{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $slack = new \spamtonprof\slack\Slack();

    // $_POST = unserializeTemp('/tempo/prospect');
    serializeTemp($_POST, '/tempo/prospect');

    $fields = $_POST['fields'];
    $token = $_POST['token_stripe'];
    $test_mode = $_POST['test_mode'];

    $fields = json_decode(stripslashes($fields));

    $choix_eleve = $fields->choix_eleve; // vaut false si prospect ou nouveau ou ref_eleve (si !prospect )
    $prenom_eleve = $fields->prenom_eleve;
    $nom_eleve = $fields->nom_eleve;
    $email_eleve = strtolower(trim($fields->email_eleve));
    $telephone_eleve = $fields->telephone_eleve;

    $prospect = boolval($fields->prospect);

    $statut_parent = $fields->statut_parent;
    $prenom_responsable = $fields->prenom_responsable;
    $nom_responsable = $fields->nom_responsable;
    $email_responsable = strtolower(trim($fields->mail_responsable));
    $telephone_responsable = $fields->telephone_responsable;
    $remarques = $fields->remarques;

    $source_traffic = $fields->source_traffic;
    $parent_saisi = boolval($fields->parent_required);

    $ref_formule = $fields->ref_formule;
    $ref_niveau = $fields->ref_niveau;
    $ref_date_stage = $fields->date_stage;
    $ref_plan = $fields->plan;

    $sameEmail = $email_eleve == $email_responsable;

    $procheMg = new \spamtonprof\stp_api\StpProcheManager();
    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();

    $envoiEleve = false; // pour savoir si il faut envoyer le mail de bienvenue a l'eleve et lui creer un compte wordpress
    $envoiParent = false; // pour savoir si il faut envoyer le mail de bienvenue au parent et lui creer un compte wordpress

    $proche = false;
    $eleve = false;

    $current_user = wp_get_current_user();

    if ($email_responsable) {
        $proche = $procheMg->get(array(
            "email" => $email_responsable
        ));

        $eleve1 = $eleveMg->get(array(
            "email" => $email_responsable
        ));
    }

    if ($email_eleve) {

        $proche1 = $procheMg->get(array(
            "email" => $email_eleve
        ));

        $eleve = $eleveMg->get(array(
            "email" => $email_eleve
        ));
    }

    // pour verifier qu'un eleve n'essaye pas de s'inscrire en proche ou reciproquement

    if ($prospect) {

        if ($proche || $eleve || $eleve1 || $proche1) {

            $retour->error = true;
            $retour->message = "compte_existe_deja";

            echo (json_encode($retour));

            die();
        }
    }

    if (! $prospect) {

        if ($choix_eleve != 'nouveau') {} else {
            if ($eleve) {

                $retour->error = true;
                $retour->message = "eleve_existe_deja";

                echo (json_encode($retour));

                die();
            }
            if ($proche1) {

                $retour->error = true;
                $retour->message = "parent_pas_eleve";

                echo (json_encode($retour));

                die();
            }
        }
    }
    // étape - 2 : récupération de la formule et du plan

    $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
    $planMg = new \spamtonprof\stp_api\StpPlanManager();
    $formule = $formuleMg->get(array(
        'ref_formule' => $ref_formule
    ));

    $plan = $planMg->get(array(
        'ref_plan' => $ref_plan
    ));

    // étape -1 : récupération du prof

    $profMg = new \spamtonprof\stp_api\StpProfManager();
    $prof = $profMg->get(array(
        'ref_prof' => 59
    ));

    $now = new DateTime(null, new DateTimeZone("Europe/Paris"));

    // etape 0 : recuperer le proche (on l'ajoute si prospect sinon on le recupere)

    $compteMg = new \spamtonprof\stp_api\StpCompteManager();
    $compte = null;
    if ($prospect) {

        if ($parent_saisi) {

            $proche = new \spamtonprof\stp_api\StpProche(array(
                'email' => $email_responsable,
                'prenom' => $prenom_responsable,
                'nom' => $nom_responsable,
                'telephone' => $telephone_responsable,
                'statut_proche' => $statut_parent
            ));

            $proche = $procheMg->add($proche);

            if ($proche) {

                $compte = new \spamtonprof\stp_api\StpCompte(array(
                    'date_creation' => $now,
                    'ref_proche' => $proche->getRef_proche()
                ));
            } else {

                $compte = new \spamtonprof\stp_api\StpCompte(array(
                    'date_creation' => $now
                ));
            }

            $compte = $compteMg->add($compte);
        }
    } else {

        $compte = $compteMg->get(array(
            'ref_compte_wp' => $current_user->ID
        ));

        // etape 1 : on recupere le proche
        $current_user = wp_get_current_user();

        $compteMg = new \spamtonprof\stp_api\StpCompteManager();
        $compte = $compteMg->get(array(
            'ref_compte_wp' => $current_user->ID
        ));

        $proche = $procheMg->get(array(
            'ref_proche' => $compte->getRef_proche()
        ));
    }

    // étape 3 : faire le paiement
    $email_client = $email_eleve;
    if ($email_responsable) {
        $email_client = $email_responsable;
    }

    $stripe = new \spamtonprof\stp_api\StripeManager($test_mode);

    $plan_stripe_id = $plan->getRef_plan_stripe();
    $prof_stripe_id = $prof->getStripe_id();
    if ($test_mode == 'true') {
        $plan_stripe_id = $plan->getRef_plan_stripe_test();
        $prof_stripe_id = $prof->getStripe_id_test();
    }

    $ret_stripe = $stripe->addInstallmentPlan($email_client, $token, $plan_stripe_id, $prof_stripe_id, $compte);

    if ($ret_stripe) {
        $customer_id_stripe = $ret_stripe["cusId"];
        $subs_id_stripe = $ret_stripe["subId"];
    } else {
        $compteMg->delete($compte);
        $procheMg->delete($proche);

        $retour->error = true;
        $retour->message = "erreur_paiement";
        echo (json_encode($retour));
        die();
    }

    // etape 4 : ajout de l'eleve

    $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();
    $niveau = $niveauMg->get(array(
        'ref_niveau' => $ref_niveau
    ));

    $parent_required = false;
    if ($proche) {
        $parent_required = true;
    }

    if ($prospect || $choix_eleve == 'nouveau') {
        $eleve = new \spamtonprof\stp_api\StpEleve(array(
            'email' => $email_eleve,
            'prenom' => $prenom_eleve,
            'ref_niveau' => $niveau->getRef_niveau(),
            'nom' => $nom_eleve,
            'telephone' => $telephone_eleve,
            "same_email" => $sameEmail,
            "ref_compte" => $compte->getRef_compte(),
            "parent_required" => $parent_required
        ));
        $eleve = $eleveMg->add($eleve);

        $eleve->setSeq_email_parent_essai(0); // pour dire qu'il n'est pas encore dans la liste d'essai
        $eleveMg->updateSeqEmailParentEssai($eleve);
    } else {

        $eleve = $eleveMg->get(array(
            "ref_eleve" => $choix_eleve
        ));

        $eleve->setRef_niveau($niveau->getRef_niveau());
        $eleveMg->updateRefNiveau($eleve);
    }

    // etape 5 : savoir a qui envoyer le mail de bienvenu et a qui creer les comptes wp

    $eleve->setHasToSend();

    $envoiEleve = $eleve->getHasToSendToEleve();
    $envoiParent = $eleve->getHasToSendToParent();

    // etape 6 : creer les nouveaux comptes wordpress

    // etape 6-1 : creation du compte eleve

    if ($envoiEleve && ($prospect || $choix_eleve == 'nouveau')) {

        $passwordEleve = wp_generate_password();
        $compteEleve = array(
            'user_login' => $email_eleve,
            'user_pass' => $passwordEleve,
            'user_email' => $email_eleve,
            'role' => 'client',
            'first_name' => $eleve->getPrenom(),
            'last_name' => $eleve->getNom()
        );
        $compteEleveId = wp_insert_user($compteEleve);

        // On success
        if (! is_wp_error($compteEleveId)) {

            $eleve->setRef_compte_wp($compteEleveId);

            $eleveMg->updateRefCompteWp($eleve);

            // connexion au compte eleve pour les prochaines visites
            wp_signon(array(
                'user_login' => $email_eleve,
                'user_password' => $passwordEleve,
                'remember' => true
            ));

            // insertion du compte stp wordpress
        } else {
            $slack->sendMessages('log', array(
                'erreur de creation du compte wp eleve : ' . $email_eleve
            ));

            $retour->error = true;
            $retour->message = 'creation-compte-wp-eleve';

            echo (json_encode($retour));

            die();
        }
    }
    // etape 6-2 : creation du compte proche si il existe

    if ($envoiParent && $prospect) {

        if ($proche) {

            $passwordProche = wp_generate_password();
            $compteProche = array(
                'user_login' => $email_responsable,
                'user_pass' => $passwordProche, // When creating an user, `user_pass` is expected.,
                'user_email' => $email_responsable,
                'role' => 'client',
                'first_name' => $proche->getPrenom(),
                'last_name' => $proche->getNom()
            );
            $compteProcheId = wp_insert_user($compteProche);

            if (! is_wp_error($compteProcheId)) {

                $proche->setRef_compte_wp($compteProcheId);

                $procheMg->updateRefCompteWp($proche);

                // connexion au compte parent pour les prochaines visites
                wp_signon(array(
                    'user_login' => $email_responsable,
                    'user_password' => $passwordProche,
                    'remember' => true
                ));
            } else {

                $slack->sendMessages('log', array(
                    'erreur de creation du compte wp proche : ' . $email_responsable
                ));

                $retour->error = true;
                $retour->message = 'creation-compte-wp-eleve';

                echo (json_encode($retour));

                die();
            }
        }
    }

    // etape 7 - inserer le stage ( ou l'abonnement dans le futur peut être )

    $test = false;
    if (strpos($email_eleve, 'yopla.33mail') !== false || strpos($email_eleve, 'test') !== false || LOCAL) {
        $test = true;
    }

    $stageMg = new \spamtonprof\stp_api\StpStageManager();

    $params_stage = array(
        'ref_eleve' => $eleve->getRef_eleve(),
        'ref_plan' => $plan->getRef_plan(),
        'ref_formule' => $plan->getRef_formule(),
        'ref_date_stage' => $ref_date_stage,
        'date_inscription' => $now->format(PG_DATETIME_FORMAT),
        'remarque_inscription' => $remarques,
        'ref_prof' => $prof->getRef_prof(),
        'ref_compte' => $compte->getRef_compte(),
        'subs_id' => $subs_id_stripe,
        'test' => $test
    );

    $stage = $stageMg->add(new \spamtonprof\stp_api\StpStage($params_stage));

    if ($proche) {

        $stage->setRef_proche($proche->getRef_proche());
        $stageMg->updateRefProche($stage);
    }

    // etape 9 - envoi d'un message dans slack pour dire qu'il y a une attribution de prof en attente

    if ($proche) {

        $messages = array(
            "Un nouveau stage vient d'être facturé : " . $formule->getFormule(),
            "------ Eleve ----- ",
            "Email élève : " . $eleve->getEmail(),
            "Prénom élève : " . utf8_encode($eleve->getPrenom()),
            "Nom élève : " . utf8_encode($eleve->getNom()),
            "Niveau élève : " . $niveau->getNiveau(),
            "Téléphone élève :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "------ Parent ----- ",
            "Email parent : " . $proche->getEmail(),
            "Prénom parent : " . utf8_encode($proche->getPrenom()),
            "Nom parent : " . utf8_encode($proche->getNom()),
            "Téléphone parent :" . $proche->getTelephone(),
            "Remarque :" . $stage->getRemarque_inscription(),
            "Source traffic : " . $source_traffic
        );
    } else {
        $messages = array(
            "Un nouveau stage vient d'être facturé " . $formule->getFormule(),
            "------ étudiant/Adulte ----- ",
            "Email : " . $eleve->getEmail(),
            "Prénom : " . utf8_encode($eleve->getPrenom()),
            " Nom : " . utf8_encode($eleve->getNom()),
            "Niveau : " . $niveau->getNiveau(),
            "Téléphone :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "Remarque :" . $stage->getRemarque_inscription(),
            "Source traffic : " . $source_traffic
        );
    }
    $messages[] = " ---------- ";
    $messages[] = "[URGENT] : Rendez vous dans le back office pour lui attribuer un prof";
    $messages[] = "et mettre un check sur le message dès que c'est fait !";

    $slack->sendMessages("stage-reserve", $messages);

    // étape 10 - envoi d'un mail de bienvenue et de mise en attente au parent et de l'élève

    $expeMg = new \spamtonprof\stp_api\StpExpeManager();
    $expe = $expeMg->get("info@spamtonprof.com");
    $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
    $smtp = $smtpMg->get(array(
        "ref_smtp_server" => $smtpMg::smtp2Go
    ));

    $dateFormuleManager = new \spamtonprof\stp_api\StpDateFormuleManager();
    $dateFormule = $dateFormuleManager->get(array(
        "ref_date_formule" => $ref_date_stage
    ));

    if ($envoiEleve) {
        $slack->sendMessages('log', array(
            'envoi eleve dedans'
        ));
        $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/stage_ete_eleve.txt");
        $body_eleve = str_replace("[[prenom-eleve]]", $eleve->getPrenom(), $body_eleve);
        $body_eleve = str_replace("[[date-stage]]", lcfirst($dateFormule->getLibelle()), $body_eleve);
        $body_eleve = str_replace("[[prenom-prof]]", $prof->getPrenom(), $body_eleve);
        $smtp->sendEmail("Bienvenue " . $eleve->getPrenom(), $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }

    if ($envoiParent) {
        $slack->sendMessages('log', array(
            'envoi parent dedans'
        ));
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/stage_ete_parent.txt");

        $body_parent = str_replace("[[prenom-eleve]]", $eleve->getPrenom(), $body_parent);
        $body_parent = str_replace("[[date-stage]]", lcfirst($dateFormule->getLibelle()), $body_parent);
        $body_parent = str_replace("[[prenom-parent]]", $proche->getPrenom(), $body_parent);
        $body_parent = str_replace("[[prenom-prof]]", $prof->getPrenom(), $body_parent);

        $smtp->sendEmail("Bienvenue " . $proche->getPrenom(), $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }

    // // etape 11 : mettre a jour l'index
    // $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
    // $algoliaMg->addAbonnement($abonnement->getRef_abonnement());

    echo (json_encode($retour));
    die();
}
