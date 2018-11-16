<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans discover_week.js sur la page dont le slug est semaine-decouverte
add_action('wp_ajax_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

add_action('wp_ajax_nopriv_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

add_action('wp_ajax_ajaxAjoutEleve', 'ajaxAjoutEleve');

add_action('wp_ajax_nopriv_ajaxAjoutEleve', 'ajaxAjoutEleve');

add_action('wp_ajax_ajaxNewEssaiClient', 'ajaxNewEssaiClient');

add_action('wp_ajax_nopriv_ajaxNewEssaiClient', 'ajaxNewEssaiClient');

add_action('wp_ajax_ajaxGetFormules', 'ajaxGetFormules');

add_action('wp_ajax_nopriv_ajaxGetFormules', 'ajaxGetFormules');

add_action('wp_ajax_inscriptionEssai', 'inscriptionEssai');

add_action('wp_ajax_nopriv_inscriptionEssai', 'inscriptionEssai');

function inscriptionEssai()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $slack = new \spamtonprof\slack\Slack();

    $fields = $_POST['fields'];
    $fields = json_decode(stripslashes($fields));

    serializeTemp($fields);

    $choix_eleve = $fields->choix_eleve; // vaut false si prospect ou nouveau ou ref_eleve (si !prospect )
    $prenom_eleve = $fields->prenom_eleve;
    $nom_eleve = $fields->nom_eleve;
    $email_eleve = trim($fields->email_eleve);
    $telephone_eleve = $fields->telephone_eleve;
    $nb_matiere = $fields->nb_matiere;
    $prospect = boolval($fields->prospect);
    $remarques_matiere1 = $fields->remarques_matiere1;
    $matiere1 = $fields->matiere1;
    $remarques_matiere2 = $fields->remarques_matiere2;
    $matiere2 = $fields->matiere2;
    $remarques_matiere3 = $fields->remarques_matiere3;
    $matiere3 = $fields->matiere3;
    $remarques_matiere4 = $fields->remarques_matiere4;
    $matiere4 = $fields->matiere4;
    $remarques_matiere5 = $fields->remarques_matiere5;
    $matiere5 = $fields->matiere5;
    $statut_parent = $fields->statut_parent;
    $prenom_responsable = $fields->prenom_responsable;
    $nom_responsable = $fields->nom_responsable;
    $email_responsable = trim($fields->mail_responsable);
    $telephone_responsable = $fields->telephone_responsable;
    $remarques = $fields->remarques;
    $code_promo = $fields->code_promo;
    $parent_required = boolval($fields->parent_required);

    $matiere = json_decode(stripslashes($fields->matiere));
    $niveau = json_decode(stripslashes($fields->niveau));
    $formule = json_decode(stripslashes($fields->formule));

    // on récupère la matière, le niveau, la formule et le plan
    $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();
    $matiere = $matiereMg->get(array(
        'ref_matiere' => $matiere->ref_matiere
    ));

    $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();
    $niveau = $niveauMg->get(array(
        'ref_niveau' => $niveau->ref_niveau
    ));

    $constructor = array(
        "construct" => array(
            'defaultPlan',
            'ref_prof',
            'matieres'
        )
    );
    $formuleManager = new \spamtonprof\stp_api\StpFormuleManager();
    $formule = $formuleManager->get(array(
        'ref_formule' => $formule->ref_formule
    ), $constructor);

    $planMg = new \spamtonprof\stp_api\StpPlanManager();
    $plan = $planMg->cast($formule->getDefaultPlan());

    $sameEmail = $email_eleve == $email_responsable;

    $procheMg = new \spamtonprof\stp_api\StpProcheManager();
    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

    $envoiEleve = false; // pour savoir si il faut envoyer le mail de bienvenue à l'élève et lui créer un compte wordpress
    $envoiParent = false; // pour savoir si il faut envoyer le mail de bienvenue au parent et lui créer un compte wordpress

    $proche = false;
    $eleve = false;

    $local = false;
    if (LOCAL) {
        $local = true;
    }

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

    // pour vérifier qu'un élève n'essaye pas de s'inscrire en proche ou réciproquement

    if ($prospect) {

        if ($proche || $eleve || $eleve1 || $proche1) {

            $retour->error = true;
            $retour->message = "compte_existe_deja";

            echo (json_encode($retour));

            die();
        }
    }

    if (! $prospect) {

        if ($choix_eleve != 'nouveau') {

            $formule->getRef_formule();

            $abo = $abonnementMg->get(array(
                'ref_formule' => $formule->getRef_formule(),
                'ref_eleve' => $choix_eleve
            ));

            if ($abo) {

                $retour->error = true;
                $retour->message = "essai_deja_fait";

                echo (json_encode($retour));

                die();
            }
            // on compte le nomre d'essai de l'élève
            $abos = $abonnementMg->getAll(array(
                'ref_eleve' => $choix_eleve,
                'ref_statut_abonnement' => 2
            ));

            if (count($abos) >= 1) {

                $retour->error = true;
                $retour->message = "eleve_deja_essai";

                echo (json_encode($retour));

                die();
            }
        } else {
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

        // on compte le nombre d'essais du compte

        $compteMg = new \spamtonprof\stp_api\StpCompteManager();

        $compte = $compteMg->get(array(
            'ref_compte_wp' => $current_user->ID
        ));

        $abos = $abonnementMg->getAll(array(
            'ref_compte' => $compte->getRef_compte(),
            'ref_statut_abonnement' => 2
        ));

        if (count($abos) >= 2) {

            $retour->error = true;
            $retour->message = "deja_2_essai";

            echo (json_encode($retour));

            die();
        }
    }

    $now = new DateTime(null, new DateTimeZone("Europe/Paris"));

    // étape n°1 : récupérer le proche (on l'ajoute si prospect sinon on le récupère)

    if ($prospect) {

        if ($parent_required) {

            $proche = new \spamtonprof\stp_api\StpProche(array(
                'email' => $email_responsable,
                'prenom' => $prenom_responsable,
                'nom' => $nom_responsable,
                'telephone' => $telephone_responsable,
                'statut_proche' => $statut_parent,
                'local' => $local
            ));

            $proche = $procheMg->add($proche);
        }
    } else {

        // étape n°1 : on récupère le proche
        $current_user = wp_get_current_user();
        $proche = $procheMg->get(array(
            'ref_compte_wp' => $current_user->ID
        ));
    }

    // étape n°2 : création du compte famille si prospect sinon on le récupère

    $compte = null;
    $compteMg = new \spamtonprof\stp_api\StpCompteManager();
    if ($prospect) {

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
    } else {

        $compte = $compteMg->get(array(
            'ref_compte_wp' => $current_user->ID
        ));
    }

    // étape n°3 : ajout de l'élève
    if ($prospect || $choix_eleve == 'nouveau') {
        $eleve = new \spamtonprof\stp_api\StpEleve(array(
            'email' => $email_eleve,
            'prenom' => $prenom_eleve,
            'ref_niveau' => $niveau->getRef_niveau(),
            'nom' => $nom_eleve,
            'telephone' => $telephone_eleve,
            "same_email" => $sameEmail,
            "ref_compte" => $compte->getRef_compte(),
            "parent_required" => $parent_required,
            "local" => $local
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

    // étape n°4 : savoir à qui envoyer le mail de bienvenu et à qui créer les comptes wp

    $eleve->setHasToSend();

    $envoiEleve = $eleve->getHasToSendToEleve();
    $envoiParent = $eleve->getHasToSendToParent();

    // étape n°5 : créer les nouveaux comptes wordpress

    // étape n°5-1 : création du compte élève

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

            // connexion au compte élève pour les prochaines visites
            wp_signon(array(
                'user_login' => $email_eleve,
                'user_password' => $passwordEleve,
                'remember' => true
            ));

            // insertion du compte stp wordpress
        } else {
            $slack->sendMessages('log', array(
                'erreur de création du compte wp élève : ' . $email_eleve
            ));

            $retour->error = true;
            $retour->message = 'creation-compte-wp-eleve';

            echo (json_encode($retour));

            die();
        }
    }
    // étape n°5-2 : création du compte proche si il existe

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
                    'erreur de création du compte wp proche : ' . $email_responsable
                ));

                $retour->error = true;
                $retour->message = 'creation-compte-wp-eleve';

                echo (json_encode($retour));

                die();
            }
        }
    }

    // étape n° 6 - insérer l'abonnement

    $abonnement = new \spamtonprof\stp_api\StpAbonnement(array(
        "ref_eleve" => $eleve->getRef_eleve(),
        "ref_formule" => $formule->getRef_formule(),
        "ref_statut_abonnement" => \spamtonprof\stp_api\StpStatutAbonnementManager::ESSAI,
        "date_creation" => $now,
        "remarque_inscription" => $remarques,
        "ref_plan" => $plan->getRef_plan()
    ));

    $abonnement = $abonnementMg->add($abonnement);

    $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
    $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
        "ref_abonnement" => $abonnement->getRef_abonnement(),
        "ref_statut_abo" => $abonnement->getRef_statut_abonnement()
    )));

    $abonnement->setRef_compte($compte->getRef_compte());
    $abonnementMg->updateRefCompte($abonnement);

    if ($proche) {

        $abonnement->setRef_proche($proche->getRef_proche());
        $abonnementMg->updateRefProche($abonnement);
    }

    $abonnement->setFirst_prof_assigned(false);
    $abonnementMg->updateFirstProfAssigned($abonnement);

    $abonnement->setInterruption(false);
    $abonnementMg->updateInterruption($abonnement);

    // étape n° 7 - insérer les remarques d'inscription

    $stpRemarqueMg = new \spamtonprof\stp_api\StpRemarqueInscriptionManager();

    $matieres = $formule->getMatieres();

    foreach ($matieres as $matiere) {

        if ($matiere1 == $matiere->getMatiere()) {
            $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                "ref_abonnement" => $abonnement->getRef_abonnement(),
                "remarque" => $remarques_matiere1,
                "ref_matiere" => $matiere->getRef_matiere()
            ));

            $stpRemarqueMg->add($stpRemarque);
        } else if ($matiere2 == $matiere->getMatiere()) {
            $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                "ref_abonnement" => $abonnement->getRef_abonnement(),
                "remarque" => $remarques_matiere2,
                "ref_matiere" => $matiere->getRef_matiere()
            ));

            $stpRemarqueMg->add($stpRemarque);
        } else if ($matiere3 == $matiere->getMatiere()) {
            $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                "ref_abonnement" => $abonnement->getRef_abonnement(),
                "remarque" => $remarques_matiere3,
                "ref_matiere" => $matiere->getRef_matiere()
            ));

            $stpRemarqueMg->add($stpRemarque);
        } else if ($matiere4 == $matiere->getMatiere()) {
            $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                "ref_abonnement" => $abonnement->getRef_abonnement(),
                "remarque" => $remarques_matiere4,
                "ref_matiere" => $matiere->getRef_matiere()
            ));

            $stpRemarqueMg->add($stpRemarque);
        } else if ($matiere5 == $matiere->getMatiere()) {
            $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                "ref_abonnement" => $abonnement->getRef_abonnement(),
                "remarque" => $remarques_matiere5,
                "ref_matiere" => $matiere->getRef_matiere()
            ));

            $stpRemarqueMg->add($stpRemarque);
        }
    }

    // étape n°8 - envoi d'un message dans slack pour dire qu'il y a une attribution de prof en attente
    $messages;
    if ($proche) {

        $messages = array(
            "Nouvelle inscription : bien joué la team prospection !!",
            "------ Eleve ----- ",
            "Email élève : " . $eleve->getEmail(),
            "Prénom élève : " . $eleve->getPrenom(),
            " Nom élève : " . $eleve->getNom(),
            "Niveau élève : " . $niveau->getNiveau(),
            "Téléphone élève :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "------ Parent ----- ",
            "Email parent : " . $proche->getEmail(),
            "Prénom parent : " . $proche->getPrenom(),
            " Nom parent : " . $proche->getNom(),
            "Téléphone parent :" . $proche->getTelephone(),
            "Remarque :" . $abonnement->getRemarque_inscription()
        );
    } else {
        $messages = array(
            "Nouvelle inscription : bien joué la team prospection !!",
            "------ Étudiant/Adulte ----- ",
            "Email : " . $eleve->getEmail(),
            "Prénom : " . $eleve->getPrenom(),
            " Nom : " . $eleve->getNom(),
            "Niveau : " . $niveau->getNiveau(),
            "Téléphone :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "Remarque :" . $abonnement->getRemarque_inscription()
        );
    }
    $messages[] = " ---------- ";
    $messages[] = "[URGENT] : Rendez vous dans le back office pour lui attribuer un prof";
    $messages[] = "et mettre un check sur le message dès que c'est fait !";

    $slack->sendMessages("inscription-essai", $messages);

    // étape n°9 - envoi d'un mail de bienvenue et de mise en attente au parent et à l'élève

    $profResponsable = $formule->getProf()->getPhrase_responsable();

    $expeMg = new \spamtonprof\stp_api\StpExpeManager();
    $expe = $expeMg->get("info@spamtonprof.com");
    $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
    $smtp = $smtpMg->get(array(
        "ref_smtp_server" => $smtpMg::smtp2Go
    ));

    if ($envoiEleve) {
        $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-eleve.html");
        $body_eleve = str_replace("[prof-responsable]", $profResponsable, $body_eleve);
        $smtp->sendEmail("Bienvenue " . $eleve->getPrenom(), $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }

    if ($envoiParent) {
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-parent.html");
        $body_parent = str_replace("[prof-responsable]", $profResponsable, $body_parent);
        $body_parent = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_parent);

        $smtp->sendEmail("Bienvenue " . $proche->getPrenom(), $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }

    echo (json_encode($retour));

    // étape n°10 : mettre à jour l'index
    $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
    $algoliaMg->addAbonnement($abonnement->getRef_abonnement());

    die();
}

function ajaxGetFormules()
{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $slack = new \spamtonprof\slack\Slack();

    $slack->sendMessages('log', $_POST);

    serializeTemp($_POST);

    $niveau = $_POST['niveau'];
    $matiere = $_POST['matiere'];

    // on cherche le niveau
    $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();
    $niveau = $niveauMg->get(array(
        'niveau' => $niveau
    ));

    if (! $niveau) {
        $retour->error = true;
        $retour->message = utf8_encode("Impossible de trouver ce niveau. Veuillez essayer d'en saisir un autre ");
        echo (json_encode($retour));
        die();
    }

    // on cherche la matiere
    $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();
    $matiere = $matiereMg->get(array(
        'matiere_complet' => $matiere
    ));

    if (! $matiere) {
        $retour->error = true;
        $retour->message = utf8_encode("Impossible de trouver cette matière. Veuillez essayer d'en saisir une autre ");
        echo (json_encode($retour));
        die();
    }

    // on trouve les formules correspondants au niveau et à la matière
    $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

    $constructor = array(
        "construct" => array(
            'defaultPlan',
            'ref_prof',
            'matieres'
        )
    );

    $formules = $formuleMg->getAll(array(
        'classe' => $niveau->getSigle(),
        'matiere' => $matiere->getMatiere()
    ), $constructor);

    $retour->niveau = $niveau;
    $retour->matiere = $matiere;
    $retour->formules = $formules;

    echo (json_encode($retour));

    die();
}

function ajaxAjoutEleve()

{
    header('Content-type: application/json');

    $retour = new \stdClass();

    $retour->error = false;
    $retour->message = "ok";

    serializeTemp($_POST);

    $prenomEleve = $_POST["prenomEleve"];
    $nomEleve = $_POST["nomEleve"];
    $emailEleve = trim($_POST["emailEleve"]);
    $phoneEleve = trim($_POST["phoneEleve"]);
    $classe = $_POST["classe"];

    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
    $parentMg = new \spamtonprof\stp_api\StpProcheManager();
    $compteMg = new \spamtonprof\stp_api\StpCompteManager();
    $classeMg = new \spamtonprof\stp_api\StpClasseManager();

    $eleve = $eleveMg->get(array(
        "email" => $emailEleve
    ));

    if ($eleve) {

        $retour->error = true;
        $retour->message = "compte_existe_deja";

        echo (json_encode($retour));

        die();
    } else {

        // étape n°1 : on récupère le compte
        $current_user = wp_get_current_user();
        $compte = $compteMg->get(array(
            'ref_compte_wp' => $current_user->ID
        ));
        // étape n°2 : recherche du parent si il existe
        $parent = $parentMg->get(array(
            "ref_proche" => $compte->getRef_proche()
        ));

        $sameEmail = false;
        if ($parent) {
            if ($parent->getEmail() == $emailEleve) {
                $sameEmail = true;
            }
        }

        // étape n°2 : classe
        $classe = $classeMg->get(array(
            "ref_classe" => $classe
        ));

        // étape n°3 : ajout de l'élève
        $eleve = new \spamtonprof\stp_api\StpEleve(array(
            'email' => $emailEleve,
            'prenom' => $prenomEleve,
            'ref_classe' => $classe->getRef_classe(),
            'nom' => $nomEleve,
            'telephone' => $phoneEleve,
            "same_email" => $sameEmail,
            "ref_profil" => $classe->getRef_profil(),
            "ref_compte" => $compte->getRef_compte()
        ));
        $eleve = $eleveMg->add($eleve);

        $eleve->setSeq_email_parent_essai(0); // pour dire qu'il n'est pas encore dans la liste d'essai
        $eleve = $eleveMg->updateSeqEmailParentEssai($eleve);

        // étape n°4 : création du compte wp

        if ($eleve->hasToSendToEleve()) {

            $passwordEleve = wp_generate_password();
            $compteEleve = array(
                'user_login' => $eleve->getEmail(),
                'user_pass' => $passwordEleve,
                'user_email' => $eleve->getEmail(),
                'role' => 'client',
                'first_name' => $eleve->getPrenom(),
                'last_name' => $eleve->getNom()
            );
            $compteEleveId = wp_insert_user($compteEleve);

            // On success
            if (! is_wp_error($compteEleveId)) {

                $eleve->setRef_compte_wp($compteEleveId);

                $eleveMg->updateRefCompteWp($eleve);
            } else {
                $slack = new \spamtonprof\slack\Slack();
                $slack->sendMessages('log', array(
                    'erreur de création du compte wp élève : ' . $emailEleve
                ));

                $retour->error = true;
                $retour->message = 'creation-compte-wp-eleve';

                echo (json_encode($retour));

                die();
            }
        }

        $retour->eleve = $eleve;
        echo (json_encode($retour));
        die();
    }
}

/* pour gérer la soumission du formulaire d'essai */
function ajaxAfterSubmissionEssai()

{
    header('Content-type: application/json');

    $retour = new \stdClass();

    $retour->error = false;
    $retour->message = "ok";

    $slack = new \spamtonprof\slack\Slack();

    $prenomEleve = $_POST["prenomEleve"];
    $nomEleve = $_POST["nomEleve"];
    $emailEleve = trim($_POST["emailEleve"]);
    $phoneEleve = trim($_POST["phoneEleve"]);
    $profil = $_POST["profil"];
    $classe = $_POST["classe"];
    $matieres = $_POST["matieres"];

    $chapterMaths = $_POST["chapterMaths"];
    $lacuneMaths = $_POST["lacuneMaths"];
    $noteMaths = $_POST["noteMaths"];
    $chapterPhysique = $_POST["chapterPhysique"];
    $lacunePhysique = $_POST["lacunePhysique"];
    $notePhysique = $_POST["notePhysique"];
    $chapterFrench = $_POST["chapterFrench"];
    $lacuneFrench = $_POST["lacuneFrench"];
    $noteFrench = $_POST["noteFrench"];
    $statutProche = $_POST["proche"];
    $prenomProche = $_POST["prenomProche"];
    $nomProche = $_POST["nomProche"];
    $mailProche = trim($_POST["mailProche"]);
    $phoneProche = trim($_POST["phoneProche"]);
    $remarque = $_POST["remarque"];
    $code = $_POST["code"];
    $teleprospection = $_POST["teleprospection"];

    $maths = false;
    $physique = false;
    $francais = false;

    $sameEmail = $emailEleve == $mailProche;

    $procheMg = new \spamtonprof\stp_api\StpProcheManager();
    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();

    $envoiEleve = false; // pour savoir si il faut envoyer le mail de bienvenue à l'élève et lui créer un compte wordpress
    $envoiParent = false; // pour savoir si il faut envoyer le mail de bienvenue au parent et lui créer un compte wordpress

    $proche = false;
    if ($mailProche != "false") {

        $proche = $procheMg->get(array(
            "email" => $mailProche
        ));
    }

    $eleve = $eleveMg->get(array(
        "email" => $emailEleve
    ));

    if ($proche || $eleve) {

        $retour->error = false;
        $retour->message = "compte_existe_deja";

        echo (json_encode($retour));

        die();
    } else {

        $proche = false;
        $now = new DateTime(null, new DateTimeZone("Europe/Paris"));

        // étape n°1 : enregistrer le proche si il existe

        if ($prenomProche != "false") {

            $proche = new \spamtonprof\stp_api\StpProche(array(
                'email' => $mailProche,
                'prenom' => $prenomProche,
                'nom' => $nomProche,
                'telephone' => $phoneProche,
                'statut_proche' => $statutProche
            ));

            $proche = $procheMg->add($proche);
        }

        // étape n°1 bis : savoir à qui envoyer le mail de bienvenu et à qui créer les comptes wp

        $envoiEleve = false;
        $envoiParent = false;

        if ($proche) {

            $envoiParent = true;

            if (! $sameEmail) {

                $envoiEleve = true;
            }
        } else {

            $envoiEleve = true;
        }

        // étape n°2 : création du compte famille

        $compteMg = new \spamtonprof\stp_api\StpCompteManager();

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

        // étape n°3 : détermination de la classe

        $classeMg = new \spamtonprof\stp_api\StpClasseManager();

        $classe = $classeMg->get(array(
            "ref_classe" => $classe
        ));

        // étape n°4 : ajout de l'élève

        $eleve = new \spamtonprof\stp_api\StpEleve(array(
            'email' => $emailEleve,
            'prenom' => $prenomEleve,
            'ref_classe' => $classe->getRef_classe(),
            'nom' => $nomEleve,
            'telephone' => $phoneEleve,
            "same_email" => $sameEmail,
            "ref_profil" => $classe->getRef_profil(),
            "ref_compte" => $compte->getRef_compte()
        ));

        $eleve = $eleveMg->add($eleve);

        $eleve->setSeq_email_parent_essai(0); // pour dire qu'il n'est pas encore dans la liste d'essai
        $eleveMg->updateSeqEmailParentEssai($eleve);

        // étape n°6 : créer les nouveaux comptes wordpress

        // étape n°6-1 : création du compte élève

        if ($envoiEleve) {

            $passwordEleve = wp_generate_password();
            $compteEleve = array(
                'user_login' => $emailEleve,
                'user_pass' => $passwordEleve,
                'user_email' => $emailEleve,
                'role' => 'client',
                'first_name' => $eleve->getPrenom(),
                'last_name' => $eleve->getNom()
            );
            $compteEleveId = wp_insert_user($compteEleve);

            // On success
            if (! is_wp_error($compteEleveId)) {

                $eleve->setRef_compte_wp($compteEleveId);

                $eleveMg->updateRefCompteWp($eleve);

                // connexion au compte élève pour les prochaines visites
                wp_signon(array(
                    'user_login' => $emailEleve,
                    'user_password' => $passwordEleve,
                    'remember' => true
                ));

                // insertion du compte stp wordpress
            } else {
                $slack->sendMessages('log', array(
                    'erreur de création du compte wp élève : ' . $emailEleve
                ));

                $retour->error = true;
                $retour->message = 'creation-compte-wp-eleve';

                echo (json_encode($retour));

                die();
            }
        }
        // étape n°6-2 : création du compte proche si il existe

        if ($envoiParent) {

            if ($proche) {

                $passwordProche = wp_generate_password();
                $compteProche = array(
                    'user_login' => $mailProche,
                    'user_pass' => $passwordProche, // When creating an user, `user_pass` is expected.,
                    'user_email' => $mailProche,
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
                        'user_login' => $mailProche,
                        'user_password' => $passwordProche,
                        'remember' => true
                    ));
                } else {

                    $slack->sendMessages('log', array(
                        'erreur de création du compte wp proche : ' . $mailProche
                    ));

                    $retour->error = true;
                    $retour->message = 'creation-compte-wp-eleve';

                    echo (json_encode($retour));

                    die();
                }
            }
        }

        // étape n°7 : construire le tableau des matières

        $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();
        $matieres = explode("-", $matieres);

        $i = 0;
        for ($i; $i < count($matieres); $i ++) {
            $matiere = $matieres[$i];

            $matieres[$i] = $matiereMg->get(array(
                'matiere' => $matiere
            ));
        }

        // étape n°8 : déterminer la formule

        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

        $formule = $formuleMg->get(array(
            'classe' => $classe,
            'matieres' => $matieres
        ));

        if (! $formule) {

            $slack->sendMessages('log', array(
                'impossible de trouver la formule de cette classe : ' . $classe->getClasse()
            ));

            $retour->error = true;
            $retour->message = 'formule-not-found';

            echo (json_encode($retour));

            die();
        }

        // étape n°9 : déterminer le plan de paiement

        $planMg = new \spamtonprof\stp_api\StpPlanManager();

        $plan = $planMg->get(array(
            'ref_formule' => $formule->getRef_formule(),
            'nom' => 'defaut'
        ));

        // étape n° 10 - insérer l'abonnement

        $abonnement = new \spamtonprof\stp_api\StpAbonnement(array(
            "ref_eleve" => $eleve->getRef_eleve(),
            "ref_formule" => $formule->getRef_formule(),
            "ref_statut_abonnement" => \spamtonprof\stp_api\StpStatutAbonnementManager::ESSAI,
            "date_creation" => $now,
            "remarque_inscription" => $remarque,
            "ref_plan" => $plan->getRef_plan()
        ));

        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

        $abonnement = $abonnementMg->add($abonnement);

        $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
        $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
            "ref_abonnement" => $abonnement->getRef_abonnement(),
            "ref_statut_abo" => $abonnement->getRef_statut_abonnement()
        )));

        $abonnement->setRef_compte($compte->getRef_compte());
        $abonnementMg->updateRefCompte($abonnement);

        if ($proche) {

            $abonnement->setRef_proche($proche->getRef_proche());
            $abonnementMg->updateRefProche($abonnement);
        }

        $abonnement->setFirst_prof_assigned(false);
        $abonnementMg->updateFirstProfAssigned($abonnement);

        $abonnement->setTeleprospection($teleprospection);
        $abonnementMg->updateTeleprospection($abonnement);

        $abonnement->setInterruption(false);
        $abonnementMg->updateInterruption($abonnement);

        // étape n° 11 - insérer les remarques d'inscription

        $stpRemarqueMg = new \spamtonprof\stp_api\StpRemarqueInscriptionManager();

        foreach ($matieres as $matiere) {

            switch ($matiere->getMatiere()) {
                case "maths":
                    $maths = true;
                    $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                        "ref_abonnement" => $abonnement->getRef_abonnement(),
                        "chapitre" => $chapterMaths,
                        "difficulte" => $lacuneMaths,
                        "note" => $noteMaths,
                        "ref_matiere" => $matiere->getRef_matiere()
                    ));

                    $stpRemarqueMg->add($stpRemarque);

                    break;
                case "physique":
                    $physique = true;
                    $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                        "ref_abonnement" => $abonnement->getRef_abonnement(),
                        "chapitre" => $chapterPhysique,
                        "difficulte" => $lacunePhysique,
                        "note" => $notePhysique,
                        "ref_matiere" => $matiere->getRef_matiere()
                    ));

                    $stpRemarqueMg->add($stpRemarque);

                    break;
                case "francais":
                    $francais = true;
                    $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                        "ref_abonnement" => $abonnement->getRef_abonnement(),
                        "chapitre" => $chapterFrench,
                        "difficulte" => $lacuneFrench,
                        "note" => $noteFrench,
                        "ref_matiere" => $matiere->getRef_matiere()
                    ));

                    $stpRemarqueMg->add($stpRemarque);

                    break;
            }
        }

        // étape n°12 - envoi d'un message dans slack pour dire qu'il y a une attribution de prof en attente
        $messages;
        if ($proche) {

            $messages = array(
                "Nouvelle inscription : bien joué la team prospection !!",
                "------ Eleve ----- ",
                "Email élève : " . $eleve->getEmail(),
                "Prénom élève : " . $eleve->getPrenom(),
                " Nom élève : " . $eleve->getNom(),
                "Classe élève : " . $classe->getClasse(),
                "Téléphone élève :" . $eleve->getTelephone(),
                "Formule : " . $formule->getFormule(),
                "------ Parent ----- ",
                "Email parent : " . $proche->getEmail(),
                "Prénom parent : " . $proche->getPrenom(),
                " Nom parent : " . $proche->getNom(),
                "Téléphone parent :" . $proche->getTelephone(),
                "Amina :" . $abonnement->getTeleprospection()
            );
        } else {
            $messages = array(
                "Nouvelle inscription : bien joué la team prospection !!",
                "------ Étudiant/Adulte ----- ",
                "Email : " . $eleve->getEmail(),
                "Prénom : " . $eleve->getPrenom(),
                " Nom : " . $eleve->getNom(),
                "Classe : " . $classe->getClasse(),
                "Téléphone :" . $eleve->getTelephone(),
                "Formule : " . $formule->getFormule(),
                "Amina :" . $abonnement->getTeleprospection()
            );
        }
        $messages[] = " ---------- ";
        $messages[] = "[URGENT] : Rendez vous dans le back office pour lui attribuer un prof";
        $messages[] = "et mettre un check sur le message dès que c'est fait !";

        $slack->sendMessages("inscription-essai", $messages);

        // étape n°13 - envoi d'un mail de bienvenue et de mise en attente au parent et à l'élève

        $profResponsable = "";

        if ($maths || $physique) {

            $profResponsable = "Sébastien le responsable des profs de maths et de physique";
        }

        if ($francais) {

            $profResponsable = "Élisabeth la responsable des profs de français";
        }

        $profResponsable = utf8_encode($profResponsable);

        $expeMg = new \spamtonprof\stp_api\StpExpeManager();
        $expe = $expeMg->get("info@spamtonprof.com");
        $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
        $smtp = $smtpMg->get(array(
            "ref_smtp_server" => $smtpMg::smtp2Go
        ));

        if ($envoiEleve) {
            $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-eleve.html");
            $body_eleve = str_replace("[prof-responsable]", $profResponsable, $body_eleve);
            $smtp->sendEmail("Bienvenue " . $eleve->getPrenom(), $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }

        if ($envoiParent) {
            $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-parent.html");
            $body_parent = str_replace("[prof-responsable]", $profResponsable, $body_parent);
            $body_parent = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_parent);

            $smtp->sendEmail("Bienvenue " . $proche->getPrenom(), $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }

        echo (json_encode($retour));

        // étape n°14 : mettre à jour l'index

        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
        $algoliaMg->addAbonnement($abonnement->getRef_abonnement());

        // étape n°15 : envoyer mail amina si inscription faite avec elle

        if ($abonnement->getTeleprospection() == "oui") {

            $body = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/amina_teleprospection.txt");
            $body = str_replace("[[prenom_eleve]]", $eleve->getPrenom(), $body);
            $body = str_replace("[[nom_eleve]]", $eleve->getNom(), $body);
            $body = str_replace("[[ref_abo]]", $abonnement->getRef_abonnement(), $body);

            $smtp->sendEmail("Nouvelle inscription !! ", 'amina.harouf@gmail.com', $body, $expe->getEmail(), "Alexandre de SpamTonProf", false);
        }

        die();
    }
}

/* pour gérer la soumission du formulaire d'essai */
function ajaxNewEssaiClient()

{
    header('Content-type: application/json');

    $retour = new \stdClass();

    $retour->error = false;
    $retour->message = "ok";

    $slack = new \spamtonprof\slack\Slack();

    $refEleve = $_POST["refEleve"];
    $matieres = $_POST["matieres"];

    $chapterMaths = $_POST["chapterMaths"];
    $lacuneMaths = $_POST["lacuneMaths"];
    $noteMaths = $_POST["noteMaths"];
    $chapterPhysique = $_POST["chapterPhysique"];
    $lacunePhysique = $_POST["lacunePhysique"];
    $notePhysique = $_POST["notePhysique"];
    $chapterFrench = $_POST["chapterFrench"];
    $lacuneFrench = $_POST["lacuneFrench"];
    $noteFrench = $_POST["noteFrench"];

    $remarque = $_POST["remarque"];
    $code = $_POST["code"];

    $maths = false;
    $physique = false;
    $francais = false;

    $proche = false;
    $now = new DateTime(null, new DateTimeZone("Europe/Paris"));

    // étape n°1 :récupération de l'élève et de sa classe
    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
    $classeMg = new \spamtonprof\stp_api\StpClasseManager();

    $eleve = $eleveMg->get(array(
        "ref_eleve" => $refEleve
    ));
    $classe = $classeMg->get(array(
        "ref_classe" => $eleve->getRef_classe()
    ));

    // étape n°2 : récupération du compte famille

    $compteMg = new \spamtonprof\stp_api\StpCompteManager();

    $compte = $compteMg->get(array(
        "ref_compte" => $eleve->getRef_compte()
    ));

    // étape n°3 : récupération du proche si il existe
    $procheMg = new \spamtonprof\stp_api\StpProcheManager();
    if ($compte->getRef_proche()) {
        $proche = $procheMg->get(array(
            'ref_proche' => $compte->getRef_proche()
        ));
    }

    // étape n°4 : construire le tableau des matières

    $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();
    $matieres = explode("-", $matieres);

    $i = 0;
    for ($i; $i < count($matieres); $i ++) {
        $matiere = $matieres[$i];

        $matieres[$i] = $matiereMg->get(array(
            'matiere' => $matiere
        ));
    }

    // étape n°5 : déterminer la formule

    $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

    $formule = $formuleMg->get(array(
        'classe' => $classe,
        'matieres' => $matieres
    ));

    if (! $formule) {

        $slack->sendMessages('log', array(
            'impossible de trouver la formule de cette classe : ' . $classe->getClasse()
        ));

        $retour->error = true;
        $retour->message = 'formule-not-found';

        echo (json_encode($retour));

        die();
    }

    // étape n°6 : déterminer le plan de paiement

    $planMg = new \spamtonprof\stp_api\StpPlanManager();

    $plan = $planMg->get(array(
        'ref_formule' => $formule->getRef_formule(),
        'nom' => 'defaut'
    ));

    // étape n° 7 - insérer l'abonnement

    $abonnement = new \spamtonprof\stp_api\StpAbonnement(array(
        "ref_eleve" => $eleve->getRef_eleve(),
        "ref_formule" => $formule->getRef_formule(),
        "ref_statut_abonnement" => \spamtonprof\stp_api\StpStatutAbonnementManager::ESSAI,
        "date_creation" => $now,
        "remarque_inscription" => $remarque,
        "ref_plan" => $plan->getRef_plan()
    ));

    $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

    $abonnement = $abonnementMg->add($abonnement);

    $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
    $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
        "ref_abonnement" => $abonnement->getRef_abonnement(),
        "ref_statut_abo" => $abonnement->getRef_statut_abonnement()
    )));

    $abonnement->setRef_compte($compte->getRef_compte());
    $abonnementMg->updateRefCompte($abonnement);

    if ($proche) {

        $abonnement->setRef_proche($proche->getRef_proche());
        $abonnementMg->updateRefProche($abonnement);
    }

    $abonnement->setFirst_prof_assigned(false);
    $abonnementMg->updateFirstProfAssigned($abonnement);

    $abonnement->setTeleprospection("non");
    $abonnementMg->updateTeleprospection($abonnement);

    $abonnement->setInterruption(false);
    $abonnementMg->updateInterruption($abonnement);

    // étape n° 8 - insérer les remarques d'inscription

    $stpRemarqueMg = new \spamtonprof\stp_api\StpRemarqueInscriptionManager();

    foreach ($matieres as $matiere) {

        switch ($matiere->getMatiere()) {
            case "maths":
                $maths = true;
                $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                    "ref_abonnement" => $abonnement->getRef_abonnement(),
                    "chapitre" => $chapterMaths,
                    "difficulte" => $lacuneMaths,
                    "note" => $noteMaths,
                    "ref_matiere" => $matiere->getRef_matiere()
                ));

                $stpRemarqueMg->add($stpRemarque);

                break;
            case "physique":
                $physique = true;
                $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                    "ref_abonnement" => $abonnement->getRef_abonnement(),
                    "chapitre" => $chapterPhysique,
                    "difficulte" => $lacunePhysique,
                    "note" => $notePhysique,
                    "ref_matiere" => $matiere->getRef_matiere()
                ));

                $stpRemarqueMg->add($stpRemarque);

                break;
            case "francais":
                $francais = true;
                $stpRemarque = new \spamtonprof\stp_api\StpRemarqueInscription(array(
                    "ref_abonnement" => $abonnement->getRef_abonnement(),
                    "chapitre" => $chapterFrench,
                    "difficulte" => $lacuneFrench,
                    "note" => $noteFrench,
                    "ref_matiere" => $matiere->getRef_matiere()
                ));

                $stpRemarqueMg->add($stpRemarque);

                break;
        }
    }

    // étape n°9 - envoi d'un message dans slack pour dire qu'il y a une attribution de prof en attente
    $messages;
    if ($proche) {

        $messages = array(
            "Une inscription supplémentaire pour ce compte : ",
            "------ Eleve ----- ",
            "Email élève : " . $eleve->getEmail(),
            "Prénom élève : " . $eleve->getPrenom(),
            " Nom élève : " . $eleve->getNom(),
            "Classe élève : " . $classe->getClasse(),
            "Téléphone élève :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "------ Parent ----- ",
            "Email parent : " . $proche->getEmail(),
            "Prénom parent : " . $proche->getPrenom(),
            " Nom parent : " . $proche->getNom(),
            "Téléphone parent :" . $proche->getTelephone(),
            "Amina :" . $abonnement->getTeleprospection()
        );
    } else {
        $messages = array(
            "Une inscription supplémentaire pour ce compte : ",
            "------ Étudiant/Adulte ----- ",
            "Email : " . $eleve->getEmail(),
            "Prénom : " . $eleve->getPrenom(),
            " Nom : " . $eleve->getNom(),
            "Classe : " . $classe->getClasse(),
            "Téléphone :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "Amina :" . $abonnement->getTeleprospection()
        );
    }
    $messages[] = " ---------- ";
    $messages[] = "[URGENT] : Rendez vous dans le back office pour lui attribuer un prof";
    $messages[] = "et mettre un check sur le message dès que c'est fait !";

    $slack->sendMessages("inscription-essai", $messages);

    // étape n°10 - envoi d'un mail de bienvenue et de mise en attente au parent et à l'élève

    $profResponsable = "";

    if ($maths || $physique) {

        $profResponsable = "Sébastien le responsable des profs de maths et de physique";
    }

    if ($francais) {

        $profResponsable = "Élisabeth la responsable des profs de français";
    }

    $profResponsable = utf8_encode($profResponsable);

    $expeMg = new \spamtonprof\stp_api\StpExpeManager();
    $expe = $expeMg->get("info@spamtonprof.com");
    $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
    $smtp = $smtpMg->get(array(
        "ref_smtp_server" => $smtpMg::smtp2Go
    ));

    if ($eleve->hasToSendToEleve()) {
        $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-eleve.html");
        $body_eleve = str_replace("[prof-responsable]", $profResponsable, $body_eleve);
        $smtp->sendEmail("Bienvenue " . $eleve->getPrenom(), $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }

    if ($eleve->hasToSendToParent()) {
        $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-parent.html");
        $body_parent = str_replace("[prof-responsable]", $profResponsable, $body_parent);
        $body_parent = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_parent);

        $smtp->sendEmail("Bienvenue " . $proche->getPrenom(), $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
    }

    // étape n°11 : mettre à jour l'index

    $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
    $algoliaMg->addAbonnement($abonnement->getRef_abonnement());

    echo (json_encode($retour));
    die();
}
