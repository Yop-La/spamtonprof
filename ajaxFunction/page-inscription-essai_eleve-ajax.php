<?php
use spamtonprof\stp_api;

use spamtonprof\stp_api\CampaignManager;
use spamtonprof\stp_api\Abonnement;
use spamtonprof\stp_api\AbonnementManager;

// toutes ces fonction seront �x�cut�s par un appel ajax r�alis� dans inscription-essai-eleve.js sur la/les page(s) d'inscription �l�ve - parent

add_action('wp_ajax_ajaxHasToLogEmailEleve', 'ajaxHasToLogEmailEleve');

add_action('wp_ajax_nopriv_ajaxHasToLogEmailEleve', 'ajaxHasToLogEmailEleve');

add_action('wp_ajax_ajaxAccountLimit', 'ajaxAccountLimit');

add_action('wp_ajax_nopriv_ajaxAccountLimit', 'ajaxAccountLimit');

/* sert � envoyer la liste de compte apr�s la saisie de l'adresse mail sur la page de paiement */
function ajaxHasToLogEmailEleve()

{
    header('Content-type: application/json');
    
    $accountManager = new \spamtonprof\stp_api\AccountManager();
    
    $accountsEleve = $accountManager->getListEleve($_POST["email"]);
    
    $accountsParent = $accountManager->getListParent($_POST["email"]);
    
    $nbAccountEleve = count($accountsEleve);
    
    $nbAcountParent = count($accountsParent);
    
    $hasTolog = false;
    
    if ($nbAccountEleve == 0 && $nbAcountParent <= 1) {
        $hasTolog = false;
    } else {
        $hasTolog = true;
    }
    
    echo (json_encode($hasTolog));
    
    die();
}

/**
 * 
 *  pour savoir si le nombre de compte par parent est atteinte ( sup�rieur ou �gale � 2 )
 *  � partir de l'adresse email
 */

function ajaxAccountLimit()

{
    header('Content-type: application/json');
    
    $accountManager = new \spamtonprof\stp_api\AccountManager();
    
    $accounts = $accountManager->getList($_POST["email"]);
    
    $accoutLimit = true;
    
    if (count($accounts) >= 2) {
        $accoutLimit = true;
    } else {
        $accoutLimit = false;
    }
    
    echo (json_encode($accoutLimit));
    
    die();
}

