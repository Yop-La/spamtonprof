<?php
use spamtonprof\stp_api\stpCompteWordpressManager;

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans discover_week.js sur la page dont le slug est semaine-decouverte

add_action('wp_ajax_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

add_action('wp_ajax_nopriv_ajaxAfterSubmissionEssai', 'ajaxAfterSubmissionEssai');

/* pour gérer la soumission du formulaire d'essai */
function ajaxAfterSubmissionEssai()

{
    header('Content-type: application/json');
    
    /* récupération des variables */
    $prenomEleve = $_POST["prenomEleve"];
    $nomEleve = $_POST["nomEleve"];
    $emailEleve = trim($_POST["emailEleve"]);
    $phoneEleve = trim($_POST["phoneEleve"]);
    $profil = $_POST["profil"];
    $classe1 = $_POST["classe1"];
    $classe2 = $_POST["classe2"];
    $classe3 = $_POST["classe3"];
    $classe4 = $_POST["classe4"];
    $chapterMaths = $_POST["chapterMaths"];
    $lacuneMaths = $_POST["lacuneMaths"];
    $noteMaths = $_POST["noteMaths"];
    $chapterPhysique = $_POST["chapterPhysique"];
    $lacunePhysique = $_POST["lacunePhysique"];
    $notePhysique = $_POST["notePhysique"];
    $chapterFrench = $_POST["chapterFrench"];
    $lacuneFrench = $_POST["lacuneFrench"];
    $noteFrench = $_POST["noteFrench"];
    $proche = $_POST["proche"];
    $prenomProche = $_POST["prenomProche"];
    $nomProche = $_POST["nomProche"];
    $mailProche = trim($_POST["mailProche"]);
    $phoneProche = trim($_POST["phoneProche"]);
    $remarque = $_POST["remarque"];
    $code = $_POST["code"];
    $mathsCoche = $_POST["mathsCoche"];
    $physiqueCoche = $_POST["physiqueCoche"];
    $frenchCoche = $_POST["frenchCoche"];
    
    $account = false; // @todostp checher l'existence du compte
    
    $slack = new \spamtonprof\slack\Slack();
    
    if ($account) {} else {
        
        $proche = false;
        $now = new DateTime(null, new DateTimeZone("Europe/Paris"));
        $classe = false;
        $error = false;
        $compte;
        
        // étape n°1 : enregistrer le proche si il existe
        
        if ($prenomProche != "false") {
            
            $procheMg = new \spamtonprof\stp_api\stpProcheManager();
            
            $proche = new \spamtonprof\stp_api\stpProche(array(
                'email' => $mailProche,
                'prenom' => $prenomProche,
                'nom' => $nomProche,
                'telephone' => $phoneProche
            ));
            
            $proche = $procheMg->add($proche);
        }
        
        // étape n°2 : création du compte famille
        
        $compteMg = new \spamtonprof\stp_api\stpCompteFamilleManager();
        
        if ($proche) {
            
            $compte = new \spamtonprof\stp_api\stpCompteFamille(array(
                'date_creation' => $now,
                'ref_proche' => $proche->getRef_proche()
            ));
        } else {
            
            $compte = new \spamtonprof\stp_api\stpCompteFamille(array(
                'date_creation' => $now
            ));
        }
        
        $compte = $compteMg->add($compte);
        
        // étape n°4 : détermination de la classe
        
        if ($classe1 != 'false') {
            $classe = $classe1;
            $slack->sendMessages('log', array(
                'classe1 : ' . $classe1
            ));
        }
        if ($classe2 != 'false') {
            $classe = $classe2;
            $slack->sendMessages('log', array(
                'classe2 : ' . $classe2
            ));
        }
        if ($classe3 != 'false') {
            $classe = $classe3;
            $slack->sendMessages('log', array(
                'classe3 : ' . $classe3
            ));
        }
        if ($classe4 != 'false') {
            $classe = $classe4;
            $slack->sendMessages('log', array(
                'classe4 : ' . $classe4
            ));
        }
        
        $classeMg = new \spamtonprof\stp_api\stpClasseManager();
        
        $classe = $classeMg->get($classe);
        
        $slack->sendMessages('log', array(
            'ref_compte_famille : ' . $compte->getRef_compte_famille()
        ));
        $slack->sendMessages('log', array(
            'email eleve : ' . $emailEleve
        ));
        $slack->sendMessages('log', array(
            'prenom eleve : ' . $prenomEleve
        ));
        $slack->sendMessages('log', array(
            'ref_classe : ' . $classe->getRef_classe()
        ));
        $slack->sendMessages('log', array(
            'nom eleve : ' . $nomEleve
        ));
        $slack->sendMessages('log', array(
            'phone eleve : ' . $phoneEleve
        ));
        
        // étape n°5 : ajout de l'élève
        
        $eleveMg = new \spamtonprof\stp_api\stpEleveManager();
        
        $eleve = new \spamtonprof\stp_api\stpEleve(array(
            'ref_compte_famille' => $compte->getRef_compte_famille(),
            'email' => $emailEleve,
            'prenom' => $prenomEleve,
            'ref_classe' => $classe->getRef_classe(),
            'nom' => $nomEleve,
            'telephone' => $phoneEleve
        ));
        
        $eleve = $eleveMg->add($eleve);
        
        // étape n°6 : créer les nouveaux comptes wordpress
        
        // étape n°6-1 : création du compte élève
        
        $compteWpMg = new \spamtonprof\stp_api\stpCompteWordpressManager();
        
        $passwordEleve = wp_generate_password();
        $compteEleve = array(
            'user_login' => $emailEleve,
            'user_pass' => $passwordEleve, // When creating an user, `user_pass` is expected.,
            'user_email' => $emailEleve,
            'role' => 'client'
        );
        $compteEleveId = wp_insert_user($compteEleve);
        
        // On success
        if (! is_wp_error($compteEleveId)) {
            
            $stpCompteWp = new \spamtonprof\stp_api\stpCompteWordpress(array(
                'ref_wp' => $compteEleveId,
                'ref_compte_famille' => $compte->getRef_compte_famille()
            ));
            
            $compteWpMg->add($stpCompteWp);
            
            $slack->sendMessages('log', array(
                'password eleve : ' . $passwordEleve
            ));
            
            // insertion du compte stp wordpress
        } else {
            $slack->sendMessages('log', array(
                'erreur de création du compte wp élève : ' . $emailEleve
            ));
            $error = 'creation-compte-wp-eleve';
        }
        
        // étape n°6-2 : création du compte proche si il existe
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
                $stpCompteWp = new \spamtonprof\stp_api\stpCompteWordpress(array(
                    'ref_wp' => $compteProcheId,
                    'ref_compte_famille' => $compte->getRef_compte_famille()
                ));
                
                $compteWpMg->add($stpCompteWp);
                
                $slack->sendMessages('log', array(
                    'password parent : ' . $passwordProche
                ));
                
                
            } else {
                

                $slack->sendMessages('log', array(
                    'erreur de création du compte wp proche : ' . $mailProche
                ));
                $error = 'creation-compte-wp-eleve';
            }
        }
        
        // étape n°7 : trouver les matières souscrites
        
        $matiereMg = new \spamtonprof\stp_api\stpMatiereManager();
        $matieres = [];
        
        
        if($frenchCoche == "1"){
            
            $matieres[] = $matiereMg -> get(array('matiere' => 'francais'));
            
        }
        if($mathsCoche == "1"){
            
            $matieres[] = $matiereMg -> get(array('matiere' => 'maths'));
            
        }
        if($physiqueCoche == "1"){
            
            $matieres[] = $matiereMg -> get(array('matiere' => 'physique'));
            
        }
        
        // étape n°8 : déterminer la formule
        
        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
        
        $formule = $formuleMg -> get(array('classe' => $classe, 'matieres' => $matieres));
        
        if(!$formule){
            
            $slack->sendMessages('log', array(
                'impossible de trouver la formule de cette classe : ' . $classe->getClasse()
            ));
            $error = 'formule-not-found';
            
        }
        
        // étape n°9 : déterminer le plan de paiement
        
        $planMg = new \spamtonprof\stp_api\StpPlanManager();
        
        $plan = $planMg -> get(array('ref_formule' => $formule->getRef_formule(), 'nom' => 'defaut'));
        
        $slack->sendMessages('log', array(
            'ref plan : ' . $plan->getRef_plan(), 'nom plan : ' . $plan ->getNom()
        ));
        
        
    }
    
    echo (json_encode("ok"));
    
    die();
}
