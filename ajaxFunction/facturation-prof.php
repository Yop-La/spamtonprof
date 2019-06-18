<?php

// toutes ces fonction seront executes par un appel ajax realise dans choisir-prof.js sur la page dont le slug est choisir-prof
add_action('wp_ajax_ajax_facturation_par_prof', 'ajax_facturation_par_prof');

add_action('wp_ajax_nopriv_ajax_facturation_par_prof', 'ajax_facturation_par_prof');

/* pour gerer la soumission du formulaire d'essai */
function ajax_facturation_par_prof()

{
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;

    serializeTemp($_POST);

    $test_mode = $_POST['test_mode'];
    $fields = $_POST['fields'];
    $fields = json_decode(stripslashes($fields));

    $email_client = $fields->email_client;
    $objet_facture = $fields->objet_facture;
    $montant = $fields->montant;
    $email_prof = $fields->email_prof;
    $montant = str_replace(',', '.', $montant);

    if (! is_numeric($montant)) {
        $retour->error = true;
        $retour->error_type = 'format_montant';
        echo (json_encode($retour));
        die();
    }

    $montant = floatval($montant);

    $email_client = trim(strtolower($email_client));
    if (! filter_var($email_client, FILTER_VALIDATE_EMAIL)) {
        $retour->error = true;
        $retour->error_type = 'format_email';
        echo (json_encode($retour));
        die();
    }

    $objet_facture = trim(strtolower($objet_facture));
    if (strlen($objet_facture) <= 10) {
        $retour->error = true;
        $retour->error_type = 'format_objet';
        echo (json_encode($retour));
        die();
    }

    $test_mode = true;
    if ($test_mode == "false") {
        $test_mode = false;
    }

    $stripe = new \spamtonprof\stp_api\StripeManager();
    $stripe->new_prof_invoice($email_client, $email_prof, $montant * 100, $objet_facture);

    $retour->fields = $fields;

    echo (json_encode($retour));

    die();
}

