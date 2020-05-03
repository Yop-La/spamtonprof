<?php

// toutes ces fonction seront executes par un appel ajax realise dans discover_week.js sur la page dont le slug est semaine-decouverte
add_action('wp_ajax_ajaxGetFormules', 'ajaxGetFormules');

add_action('wp_ajax_nopriv_ajaxGetFormules', 'ajaxGetFormules');

add_action('wp_ajax_inscriptionEssai', 'inscriptionEssai');

add_action('wp_ajax_nopriv_inscriptionEssai', 'inscriptionEssai');

add_action('wp_ajax_ajaxIsValidCoupon', 'ajaxIsValidCoupon');

add_action('wp_ajax_nopriv_ajaxIsValidCoupon', 'ajaxIsValidCoupon');

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

    $choix_eleve = $fields->choix_eleve; // vaut false si prospect ou nouveau ou ref_eleve (si !prospect )
    $prenom_eleve = $fields->prenom_eleve;
    $nom_eleve = $fields->nom_eleve;
    $email_eleve = trim($fields->email_eleve);
    $telephone_eleve = $fields->telephone_eleve;
    $nb_matiere = $fields->nb_matiere;
    $date_debut = $fields->date_debut;

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
    $source_traffic = $fields->source_traffic;
    $parent_required = boolval($fields->parent_required);

    $utm_source_stp = $fields->utm_source_stp;
    $utm_medium_stp = $fields->utm_medium_stp;
    $utm_campaign_stp = $fields->utm_campaign_stp;

    $matiere = json_decode(stripslashes($fields->matiere));
    $niveau = json_decode(stripslashes($fields->niveau));
    $formule = json_decode(stripslashes($fields->formule));

    // on recupere la matiere, le niveau, la formule et le plan
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
            // on compte le nomre d'essai de l'eleve
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

    // etape 1 : recuperer le proche (on l'ajoute si prospect sinon on le recupere)

    if ($prospect) {

        if ($parent_required) {

            $proche = new \spamtonprof\stp_api\StpProche(array(
                'email' => $email_responsable,
                'prenom' => $prenom_responsable,
                'nom' => $nom_responsable,
                'telephone' => $telephone_responsable,
                'statut_proche' => $statut_parent
            ));

            $proche = $procheMg->add($proche);
        }
    } else {

        // etape 1 : on recupere le proche
        $current_user = wp_get_current_user();

        $compte = $compteMg->get(array(
            'ref_compte_wp' => $current_user->ID
        ));

        $proche = $procheMg->get(array(
            'ref_proche' => $compte->getRef_proche()
        ));

        if (! $proche) {
            $parent_required = false;
        }
    }

    // etape 2 : creation du compte famille si prospect sinon on le recupere

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

    // etape 3 : ajout de l'eleve
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

    // etape 4 : savoir a qui envoyer le mail de bienvenu et a qui creer les comptes wp

    $eleve->setHasToSend();

    $envoiEleve = $eleve->getHasToSendToEleve();
    $envoiParent = $eleve->getHasToSendToParent();

    // etape 5 : creer les nouveaux comptes wordpress

    // etape 5-1 : creation du compte eleve

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
    // etape 5-2 : creation du compte proche si il existe

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

    // etape 6 : code promo
    $couponMg = new \spamtonprof\stp_api\StpCouponManager();
    $coupon = $couponMg->get(array(
        'name' => $code_promo
    ));

    if (! $prospect && $coupon) {

        $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
        $abos = $aboMg->getAll(array(
            'ref_coupon' => $coupon->getRef_coupon(),
            'ref_compte' => $compte->getRef_compte()
        ));

        if (count($abos) >= $coupon->getClient_limit()) {
            $coupon = false;
        }
    }

    // etape 7 - inserer l'abonnement

    $test = false;
    if (strpos($email_eleve, 'yopla.33mail') !== false || strpos($email_eleve, 'test') !== false || LOCAL) {
        $test = true;
    }

    $abonnement = new \spamtonprof\stp_api\StpAbonnement(array(
        "ref_eleve" => $eleve->getRef_eleve(),
        "ref_formule" => $formule->getRef_formule(),
        "ref_statut_abonnement" => \spamtonprof\stp_api\StpStatutAbonnementManager::ESSAI,
        "date_creation" => $now,
        "remarque_inscription" => $remarques,
        "ref_plan" => $plan->getRef_plan(),
        "test" => $test
    ));

    $abonnement = $abonnementMg->add($abonnement);

    // etape 6-1 : définir les dates de début et de fin d'essai
    $begin = \DateTime::createFromFormat(FR_DATE_FORMAT, $date_debut, new \DateTimeZone("Europe/Paris"));
    $abonnement->setDebut_essai($begin->format(PG_DATE_FORMAT));

    $end = clone $begin;

    $end = $end->add(new DateInterval('P7D'));
    $abonnement->setFin_essai($end->format(PG_DATE_FORMAT));

    $abonnementMg->updateDebutEssai($abonnement);
    $abonnementMg->updateFinEssai($abonnement);

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

    $abonnement->setUtm_campaign_stp($utm_campaign_stp);
    $abonnementMg->updateUtmCampaignStp($abonnement);

    $abonnement->setUtm_medium_stp($utm_medium_stp);
    $abonnementMg->updateUtmMediumStp($abonnement);

    $abonnement->setUtm_source_stp($utm_source_stp);
    $abonnementMg->updateUtmSourceStp($abonnement);

    $abonnement->setFirst_prof_assigned(false);
    $abonnementMg->updateFirstProfAssigned($abonnement);

    $abonnement->setInterruption(false);
    $abonnementMg->updateInterruption($abonnement);

    if ($coupon) {
        $abonnement->setRef_coupon($coupon->getRef_coupon());
        $abonnementMg->updateRefCoupon($abonnement);
    }

    // etape 8 - inserer les remarques d'inscription

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

    // etape 9 - envoi d'un message dans slack pour dire qu'il y a une attribution de prof en attente
    $messages;

    $source_trafic_app = [];
    if ($utm_source_stp) {
        $source_trafic_app["utm_source_stp"] = $utm_source_stp;
    }
    if ($utm_medium_stp) {
        $source_trafic_app["utm_medium_stp"] = $utm_medium_stp;
    }
    if ($utm_campaign_stp) {
        $source_trafic_app["utm_campaign_stp"] = $utm_campaign_stp;
    }

    $source_trafic_app = json_encode($source_trafic_app);

    if ($proche) {

        $messages = array(
            "Nouvelle inscription : bien joué la team prospection !!",
            "------ Eleve ----- ",
            "Email élève : " . $eleve->getEmail(),
            "Prénom élève : " . utf8_encode($eleve->getPrenom()),
            "Nom élève : " . utf8_encode($eleve->getNom()),
            "Niveau élève : " . utf8_encode($niveau->getNiveau()),
            "Téléphone élève :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "------ Parent ----- ",
            "Email parent : " . $proche->getEmail(),
            "Prénom parent : " . utf8_encode($proche->getPrenom()),
            "Nom parent : " . utf8_encode($proche->getNom()),
            "Téléphone parent :" . $proche->getTelephone(),
            "Remarque :" . $abonnement->getRemarque_inscription(),
            "Source trafic client: " . $source_traffic,
            "Source trafic app: " . $source_trafic_app,
            "Coupon : " . $code_promo,
            "Date de démarrage : " . $begin->format(FR_DATE_FORMAT)
        );
    } else {
        $messages = array(
            "Nouvelle inscription : bien joué la team prospection !!",
            "------ étudiant/Adulte ----- ",
            "Email : " . $eleve->getEmail(),
            "Prénom : " . utf8_encode($eleve->getPrenom()),
            " Nom : " . utf8_encode($eleve->getNom()),
            "Niveau : " . utf8_encode($niveau->getNiveau()),
            "Téléphone :" . $eleve->getTelephone(),
            "Formule : " . $formule->getFormule(),
            "Remarque :" . $abonnement->getRemarque_inscription(),
            "Source trafic client: " . $source_traffic,
            "Source trafic app: " . $source_trafic_app,
            "Coupon : " . $code_promo,
            "Date de démarrage : " . $begin->format(FR_DATE_FORMAT)
        );
    }
    $messages[] = " ---------- ";
    $messages[] = "[URGENT] : Rendez vous dans le back office pour lui attribuer un prof";
    $messages[] = "et mettre un check sur le message dès que c'est fait !";

    $slack->sendMessages("inscription-essai", $messages);

    // étape 10 - envoi d'un mail de bienvenue et de mise en attente au parent et a l'eleve

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

    // etape 11 : mettre a jour l'index
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

    // on trouve les formules correspondants au niveau et a la matiere
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

/*
 * pour verifier que le coupon saisi est valide c'est a dire
 *
 * si il existe et si le client/prospect a le droit de l'utiliser
 * si prospect -> oui, il a le droit de l'utiliser
 * si client -> ca depend
 *
 */
function ajaxIsValidCoupon()
{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';
    $retour->couponValid = false;
    $retour->statut = 'ok';

    $slack = new \spamtonprof\slack\Slack();

    $slack->sendMessages('log', $_POST);

    serializeTemp($_POST);

    $coupon = $_POST['coupon'];

    $couponMg = new \spamtonprof\stp_api\StpCouponManager();
    $coupon = $couponMg->get(array(
        'name' => $coupon
    ));

    if (! $coupon) {
        $retour->message = 'Code promo invalide';
        echo (json_encode($retour));
        die();
    }

    // si c'est un prospect ok
    $current_user = wp_get_current_user();
    if (0 == $current_user->ID) {
        $retour->message = $coupon->getDescription();
        $retour->couponValid = true;
        echo (json_encode($retour));
        die();
    }

    // cas du client connecte
    $compteMg = new \spamtonprof\stp_api\StpCompteManager();

    $compte = $compteMg->get(array(
        'ref_compte_wp' => $current_user->ID
    ));

    $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
    $abos = $aboMg->getAll(array(
        'ref_coupon' => $coupon->getRef_coupon(),
        'ref_compte' => $compte->getRef_compte()
    ));

    if (count($abos) >= $coupon->getClient_limit()) {
        $retour->statut = 'over';
        $retour->message = utf8_encode('Code promo déjà utilisé');
        echo (json_encode($retour));
        die();
    }

    $retour->message = $coupon->getDescription();
    $retour->couponValid = true;
    echo (json_encode($retour));
    die();
}