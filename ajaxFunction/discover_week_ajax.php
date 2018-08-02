<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans discover_week.js sur la page dont le slug est semaine-decouverte
add_action('wp_ajax_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

add_action('wp_ajax_nopriv_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

add_action('wp_ajax_ajaxGetProfils', 'ajaxGetProfils');

add_action('wp_ajax_nopriv_ajaxGetProfils', 'ajaxGetProfils');

add_action('wp_ajax_ajaxGetClasses', 'ajaxGetClasses');

add_action('wp_ajax_nopriv_ajaxGetClasses', 'ajaxGetClasses');

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
   
    $maths = false;
    $physique = false;
    $francais = false;
    
    $sameEmail = $emailEleve == $mailProche;
    
    $procheMg = new \spamtonprof\stp_api\stpProcheManager();
    $eleveMg = new \spamtonprof\stp_api\stpEleveManager();
    
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
        
        $compte;
        
        // étape n°1 : enregistrer le proche si il existe
        
        if ($prenomProche != "false") {
            
            $proche = new \spamtonprof\stp_api\stpProche(array(
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
        
        $compteMg = new \spamtonprof\stp_api\stpCompteManager();
        
        if ($proche) {
            
            $compte = new \spamtonprof\stp_api\stpCompte(array(
                'date_creation' => $now,
                'ref_proche' => $proche->getRef_proche()
            ));
        } else {
            
            $compte = new \spamtonprof\stp_api\stpCompte(array(
                'date_creation' => $now
            ));
        }
        
        $compte = $compteMg->add($compte);
        
        // étape n°3 : détermination de la classe
        
        $classeMg = new \spamtonprof\stp_api\stpClasseManager();
        
        $classe = $classeMg->get(array(
            "ref_classe" => $classe
        ));
        
        // étape n°4 : ajout de l'élève
        
        $eleve = new \spamtonprof\stp_api\stpEleve(array(
            'email' => $emailEleve,
            'prenom' => $prenomEleve,
            'ref_classe' => $classe->getRef_classe(),
            'nom' => $nomEleve,
            'telephone' => $phoneEleve,
            "same_email" => $sameEmail,
            "ref_profil" => $classe->getRef_profil()
        ));
        
        $eleve = $eleveMg->add($eleve);
        
        // étape n° 5 lié l'élève au compte
        
        $stpCompose = new \spamtonprof\stp_api\stpCompose(array(
            "ref_eleve" => $eleve->getRef_eleve(),
            "ref_compte" => $compte->getRef_compte()
        ));
        
        $stpComposeMg = new \spamtonprof\stp_api\stpComposeManager();
        
        $stpComposeMg->add($stpCompose);
        
        // étape n°6 : créer les nouveaux comptes wordpress
        
        // étape n°6-1 : création du compte élève
        
        if ($envoiEleve) {
            
            $passwordEleve = wp_generate_password();
            $compteEleve = array(
                'user_login' => $emailEleve,
                'user_pass' => $passwordEleve,
                'user_email' => $emailEleve,
                'role' => 'client'
            );
            $compteEleveId = wp_insert_user($compteEleve);
            
            // On success
            if (! is_wp_error($compteEleveId)) {
                
                $eleve->setRef_compte_wp($compteEleveId);
                
                $eleveMg->updateRefCompteWp($eleve);
                
                $slack->sendMessages('log', array(
                    'password eleve : ' . $passwordEleve
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
                    'role' => 'client'
                );
                $compteProcheId = wp_insert_user($compteProche);
                
                if (! is_wp_error($compteProcheId)) {
                    
                    $proche->setRef_compte_wp($compteProcheId);
                    
                    $procheMg->updateRefCompteWp($proche);
                    
                    $slack->sendMessages('log', array(
                        'password parent : ' . $passwordProche
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
        
        $matiereMg = new \spamtonprof\stp_api\stpMatiereManager();
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
        
        $abonnement = new \spamtonprof\stp_api\stpAbonnement(array(
            "ref_eleve" => $eleve->getRef_eleve(),
            "ref_formule" => $formule->getRef_formule(),
            "ref_statut_abonnement" => \spamtonprof\stp_api\stpStatutAbonnementManager::ESSAI,
            "date_creation" => $now,
            "remarque_inscription" => $remarque,
            "ref_plan" => $plan->getRef_plan()
        ));
        
        $abonnementMg = new \spamtonprof\stp_api\stpAbonnementManager();
        
        $abonnementMg->add($abonnement);
        
        // étape n° 11 - insérer les remarques d'inscription
        
        $stpRemarqueMg = new \spamtonprof\stp_api\stpRemarqueInscriptionManager();
        
        foreach ($matieres as $matiere) {
            
            switch ($matiere->getMatiere()) {
                case "maths":
                    $maths = true;
                    $stpRemarque = new \spamtonprof\stp_api\stpRemarqueInscription(array(
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
                    $stpRemarque = new \spamtonprof\stp_api\stpRemarqueInscription(array(
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
                    $stpRemarque = new \spamtonprof\stp_api\stpRemarqueInscription(array(
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
                "------ Parent ----- ",
                "Email parent : " . $proche->getEmail(),
                "Prénom parent : " . $proche->getPrenom(),
                " Nom parent : " . $proche->getNom(),
                "Téléphone parent :" . $proche->getTelephone()
            );
        } else {
            $messages = array(
                "Nouvelle inscription : bien joué la team prospection !!",
                "------ Étudiant/Adulte ----- ",
                "Email : " . $eleve->getEmail(),
                "Prénom : " . $eleve->getPrenom(),
                " Nom : " . $eleve->getNom(),
                "Classe : " . $classe->getClasse(),
                "Téléphone :" . $eleve->getTelephone()
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
        
        $expeMg = new \spamtonprof\stp_api\stpExpeManager();
        $expe = $expeMg->get("alexandre@spamtonprof.com");
        $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
        $smtp = $smtpMg->get(array(
            "ref_smtp_server" => $smtpMg::smtp2Go
        ));
        
        if ($envoiEleve) {
            $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-eleve.html");
            $body_eleve = str_replace("[prof-responsable]", $profResponsable, $body_eleve);
            $smtp->sendEmail("Bienvenue " . $eleve->getPrenom(), $eleve->getEmail(), $body_eleve, $expe->getEmail(), $expe->getFrom_name(),true);
        }
        
        if ($envoiParent) {
            $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/bienvenue-essai-parent.html");
            $body_parent = str_replace("[prof-responsable]", $profResponsable, $body_parent);
            $body_parent = str_replace("[prenom-eleve]", $eleve->getPrenom(), $body_parent);
            
            $smtp->sendEmail("Bienvenue " . $proche->getPrenom(), $proche->getEmail(), $body_parent, $expe->getEmail(), $expe->getFrom_name(),true);
        }
        
        echo (json_encode($retour));
        
        die();
    }
}

function ajaxGetProfils()
{
    header('Content-type: application/json');
    
    $stpProfilMg = new \spamtonprof\stp_api\stpProfilManager();
    
    $profils = $stpProfilMg->getAll();
    
    echo (json_encode($profils));
    
    die();
}

function ajaxGetClasses()
{
    header('Content-type: application/json');
    
    $stpProfilMg = new \spamtonprof\stp_api\stpClasseManager();
    
    $refProfil = $_POST["ref_profil"];
    
    $profils = $stpProfilMg->getAll(array(
        "ref_profil" => $refProfil
    ));
    
    echo (json_encode($profils));
    
    die();
}