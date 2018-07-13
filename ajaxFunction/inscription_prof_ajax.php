<?php
use spamtonprof\stp_api\stpCompteWordpressManager;

// toutes ces fonction seront �x�cut�s par un appel ajax r�alis� dans inscription-prof.js sur la page dont le slug est inscription-prof

add_action('wp_ajax_ajaxInscriptionProf', 'ajaxInscriptionProf');

add_action('wp_ajax_nopriv_ajaxInscriptionProf', 'ajaxInscriptionProf');

/* pour g�rer la soumission du formulaire d'inscription des profs */
function ajaxInscriptionProf()

{
    $error = false;
    $retour = "ok";
    
    $slack = new \spamtonprof\slack\Slack();
    $stpProfMg = new \spamtonprof\stp_api\stpProfManager();
    
    header('Content-type: application/json');
    
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    
    // premi�re partie : est ce que prof � d�j� un compte chez nous ?
    $accountExist = $stpProfMg->get(array(
        'email_perso' => $email
    ));
    
    // si il a d�j� un compte -> renvoyer vers page de connexion
    if ($accountExist) {
        
        $error = "account-exists";
    } else {
        
        $stpProf = $stpProfMg->add(new \spamtonprof\stp_api\stpProf(array(
            'email_perso' => $email,
            'prenom' => $prenom,
            'nom' => $nom,
            'telephone' => $mobile
        )));
        
        $stpProf -> setOnboarding(false);
        
        $stpProfMg -> updateOnboarding($stpProf);
        
        // enregistrer le prof dans la bdd
        $compteWpMg = new \spamtonprof\stp_api\stpCompteWordpressManager();
        
        // cr�er le compte wordpresss
        $password = wp_generate_password();
        $compteProf = array(
            'user_login' => $stpProf->getEmail_perso(),
            'user_pass' => $password, // When creating an user, `user_pass` is expected.,
            'user_email' => $stpProf->getEmail_perso(),
            'first_name' => $stpProf -> getPrenom(),
            'role' => 'prof'
        );
        $compteProfId = wp_insert_user($compteProf);
        
        if (! is_wp_error($compteProfId)) {
            
            $slack->sendMessages('log', array(
                'password eleve : ' . $password
            ));
            
            $stpProf->setUser_id_wp($compteProfId);
            
            $stpProfMg->updateUserIdWp($stpProf);
            
            
            wp_signon(array(
                'user_login' => $stpProf->getEmail_perso(),
                'user_password' => $password,
                'remember' => true
            ));

        } else {
            $slack->sendMessages('log', array(
                'erreur de cr�ation du compte wp prof : ' . $stpProf->getEmail_perso()
            ));
            $error = 'creation-compte-wp-prof';
        }
    }
    
    if ($error) {
        $retour = $error;
    }
    
    echo (json_encode($retour));
    
    die();
}