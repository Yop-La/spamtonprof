<?php


// toutes ces fonction seront éxécutés par un appel ajax réalisé dans inscription-prof.js sur la page dont le slug est inscription-prof

add_action('wp_ajax_ajaxInscriptionProf', 'ajaxInscriptionProf');

add_action('wp_ajax_nopriv_ajaxInscriptionProf', 'ajaxInscriptionProf');

/* pour gérer la soumission du formulaire d'inscription des profs */
function ajaxInscriptionProf()

{
    $error = false;
    $retour = "ok";
    
    $slack = new \spamtonprof\slack\Slack();
    $StpProfMg = new \spamtonprof\stp_api\StpProfManager();
    
    header('Content-type: application/json');
    
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $dob = trim($_POST['dob']);
    $sexe = trim($_POST['sexe']);
    
    // première partie : est ce que prof à déjà un compte chez nous ?
    $accountExist = $StpProfMg->get(array(
        'email_perso' => $email
    ));
    
    // si il a déjà un compte -> renvoyer vers page de connexion
    if ($accountExist) {
        
        $error = "account-exists";
        
    } else {
        
        $dob = DateTime::createFromFormat('j/m/Y', $dob);
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        
        $StpProf = $StpProfMg->add(new \spamtonprof\stp_api\StpProf(array(
            'email_perso' => $email,
            'prenom' => $prenom,
            'nom' => $nom,
            'telephone' => $mobile,
            'onboarding_step' => "step-0",
            'date_naissance' => $dob,
            'sexe' => $sexe,
            'processing_date' => $now
        )));
        
        
        
        $StpProf -> setOnboarding(false);
        
        $StpProfMg -> updateOnboarding($StpProf);
        
        
        // créer le compte wordpresss
        $password = wp_generate_password();
        $compteProf = array(
            'user_login' => $StpProf->getEmail_perso(),
            'user_pass' => $password, // When creating an user, `user_pass` is expected.,
            'user_email' => $StpProf->getEmail_perso(),
            'first_name' => $StpProf -> getPrenom(),
            'role' => 'prof'
        );
        $compteProfId = wp_insert_user($compteProf);
        
        if (! is_wp_error($compteProfId)) {
            
            $StpProf->setUser_id_wp($compteProfId);
            
            $StpProfMg->updateUserIdWp($StpProf);
            
            
            wp_signon(array(
                'user_login' => $StpProf->getEmail_perso(),
                'user_password' => $password,
                'remember' => true
            ));

            
            $slack->sendMessages('prof', array(
                " -- Inscription d'un nouveau prof : ".$StpProf->getPrenom(). "-- ",
                "Voilà les actions à mener pour terminer son inscription : ",
                " - 1°) lui attribuer une adresse spamtonprof ",
                " - 2°) mettre à jour la table stp_prof avec son adresse pro ",
                " - 3°) lui préparer sa boite mail' "
            ));
            
            
        } else {
            $slack->sendMessages('prof', array(
                'erreur de création du compte wp prof : ' . $StpProf->getEmail_perso()
            ));
            $error = 'creation-compte-wp-prof';
        }
    }
    
    if ($error) {
        $retour = $error;
    }else{
        $retour = $StpProf;
    }
    
    echo (json_encode($retour));
    
    die();
}
