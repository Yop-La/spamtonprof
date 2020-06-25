<?php

// executed by ajax on spam-express-offre pages
add_action('wp_ajax_process_step_1', 'process_step_1');

add_action('wp_ajax_nopriv_process_step_1', 'process_step_1');

add_action('wp_ajax_process_step_2', 'process_step_2');

add_action('wp_ajax_nopriv_process_step_2', 'process_step_2');

add_action('wp_ajax_process_step_3', 'process_step_3');

add_action('wp_ajax_nopriv_process_step_3', 'process_step_3');

function process_step_3()
{
    header('Content-type: application/json');
    $retour = new \stdClass();
    
    serializeTemp($_POST);

    $cmd_id_encrypted = $_POST['ref_cmd_encrypted'];
    $ref_cmd = $_POST['ref_cmd'];
    $ref_cmd = $_POST['ref_cmd'];
    $ref_offre = $_POST['ref_offre'];
    $test_mode = $_POST['test_mode'];

    $ref_cmd_to_check = encrypt_decrypt('decrypt', $cmd_id_encrypted, SECRET_KEY_URL_PAREMETER, SECRET_IV_URL_PAREMETER);

    if ($ref_cmd_to_check == false || $ref_cmd_to_check != $ref_cmd) {
        $retour->error = true;
        $retour->message = "Quelque chose ne fonctionne pas. Demandez de l'aide dans le tchat";

        echo (json_encode($retour));
        die();
    }

    $constructor = array(
        "construct" => array(
            'ref_lead',
            'ref_pole',
            'ref_prof'
        )
    );

    $cmd_mg = new \spamtonprof\stp_api\StpCmdSpamExpressManager();
    $offre_mg = new \spamtonprof\stp_api\StpOffreSpamExpressManager();

    $cmd = $cmd_mg->get($ref_cmd, $constructor);
    $offre = $offre_mg->get($ref_offre);
    $prof = $cmd->getProf();
    $lead = $cmd->getLead();

    $stripe = new \spamtonprof\stp_api\StripeManager($test_mode);

    $stripe_price = $offre->getStripe_price();
    if ($test_mode) {
        $stripe_price = $offre->getStripe_price_test();
    }

    $stripe_prof_id = $prof->get_stripe_id($test_mode);

    $checkout_session_id = $stripe->create_checkout_session_spam_express($stripe_price, $ref_cmd, $cmd_id_encrypted, $stripe_prof_id, $offre->getRef_offre(), $lead->getEmail());

    $retour->error = false;
    $retour->checkout_session_id = $checkout_session_id;

    echo (json_encode($retour));
    die();
}

function process_step_2()
{
    header('Content-type: application/json');
    $retour = new \stdClass();

    $fields = $_POST['fields'];
    $cmd_id_encrypted = $_POST['ref_cmd_encrypted'];
    $ref_cmd = $_POST['ref_cmd'];

    $fields = json_decode(stripslashes($fields));

    $demande = $fields->demande;
    $remarque = $fields->remarque;

    $ref_cmd_to_check = encrypt_decrypt('decrypt', $cmd_id_encrypted, SECRET_KEY_URL_PAREMETER, SECRET_IV_URL_PAREMETER);

    if ($ref_cmd_to_check == false || $ref_cmd_to_check != $ref_cmd) {
        $retour->error = true;
        $retour->message = "Quelque chose ne fonctionne pas. Demandez de l'aide dans le tchat";

        echo (json_encode($retour));
        die();
    }

    $cmdMg = new \spamtonprof\stp_api\StpCmdSpamExpressManager();

    $cmd = $cmdMg->get($ref_cmd);

    $cmd->setRemarque($remarque);
    $cmdMg->update_remarque($cmd);

    $cmd->setStatus("step-2");
    $cmdMg->update_status($cmd);

    $cmd->setRef_offre($demande);
    $cmdMg->update_ref_offre($cmd);

    $profMg = new \spamtonprof\stp_api\StpProfManager();
    $prof = $profMg->get(array(
        'email_stp' => 'sebastien@spamtonprof.com'
    ));

    $cmd->setRef_prof($prof->getRef_prof());
    $cmdMg->update_ref_prof($cmd);

    $retour->error = false;

    echo (json_encode($retour));

    die();
}

function process_step_1()
{
    header('Content-type: application/json');
    $retour = new \stdClass();

    $fields = $_POST['fields'];
    $fields = json_decode(stripslashes($fields));

    $prenom = $fields->prenom;
    $email = $fields->email;
    $pole = $fields->pole;
    $niveau = $fields->niveau;

    $is_update = false;
    if (array_key_exists('is_update', $_POST)) {
        $is_update = str_to_bool($_POST['is_update']);
    }

    $ref_cmd_encrypted = false;
    if (array_key_exists('ref_cmd_encrypted', $_POST)) {
        $ref_cmd_encrypted = $_POST['ref_cmd_encrypted'];
    }

    $ref_cmd = false;
    if (array_key_exists('ref_cmd', $_POST)) {
        $ref_cmd = $_POST['ref_cmd'];
    }

    if ($is_update) {
        $ref_cmd_to_check = encrypt_decrypt('decrypt', $ref_cmd_encrypted, SECRET_KEY_URL_PAREMETER, SECRET_IV_URL_PAREMETER);

        if ($ref_cmd_to_check == false || $ref_cmd_to_check != $ref_cmd) {
            $retour->error = true;
            $retour->message = "Quelque chose ne fonctionne pas. Demandez de l'aide dans le tchat";

            echo (json_encode($retour));
            die();
        }
    }

    $leadMg = new \spamtonprof\stp_api\StpLeadSpamExpressManager();
    $cmdMg = new \spamtonprof\stp_api\StpCmdSpamExpressManager();

    $cmd = false;
    if (! $is_update) { // new cmd

        $lead = $leadMg->get(array(
            'key' => 'get_by_email',
            array(
                'email' => $email
            )
        ));

        if (! $lead) {

            $lead = $leadMg->add(new \spamtonprof\stp_api\StpLeadSpamExpress(array(
                'email' => $email,
                'name' => $prenom
            )));
        }

        $cmd = $cmdMg->add(new \spamtonprof\stp_api\StpCmdSpamExpress(array(
            'ref_lead' => $lead->getRef_lead(),
            'ref_cat_scolaire' => $niveau,
            'status' => 'step-1',
            'ref_pole' => $pole
        )));
    } else {

        $constructor = array(
            "construct" => array(
                'ref_lead',
                'offres'
            )
        );

        $cmd = $cmdMg->get($ref_cmd, $constructor);
        $lead = $cmd->getLead();

        if ($prenom != $lead->getName()) {

            $lead->setName($prenom);
            $leadMg->update_name($lead);
        }

        if ($email != $lead->getEmail()) {
            $lead->setEmail($email);
            $leadMg->update_email($lead);
        }

        if ($cmd->getRef_cat_scolaire() != $niveau) {
            $cmd->setRef_cat_scolaire($niveau);
            $cmdMg->update_ref_cat_scolaire($cmd);
        }

        if ($cmd->getRef_pole() != $pole) {
            $cmd->setRef_pole($pole);
            $cmdMg->update_ref_pole($cmd);
        }
    }

    $retour->error = false;
    $retour->cmd = $cmd;
    $retour->param_next_page = encrypt_decrypt('encrypt', $cmd->getRef_cmd(), SECRET_KEY_URL_PAREMETER, SECRET_IV_URL_PAREMETER);

    echo (json_encode($retour));

    die();
} 

 