<?php

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans edit_lbc_text.js
add_action('wp_ajax_ajaxLoadTexts', 'ajaxLoadTexts');

add_action('wp_ajax_nopriv_ajaxLoadTexts', 'ajaxLoadTexts');

add_action('wp_ajax_ajaxUpdateLbcText', 'ajaxUpdateLbcText');

add_action('wp_ajax_nopriv_ajaxUpdateLbcText', 'ajaxUpdateLbcText');

function ajaxLoadTexts()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    // $fields = $_POST['fields'];
    // $fields = json_decode(stripslashes($fields));
    // $text_category = $fields->text_category;

    $ref_type_texte = $_POST['ref_type_texte'];

    $textMg = new \spamtonprof\stp_api\LbcTexteManager();
    $textes = $textMg->getAll(array(
        'ref_type_texte' => $ref_type_texte
    ));

    $retour->text_category = $ref_type_texte;
    $retour->textes = $textes;

    echo (json_encode($retour));

    die();
}

function ajaxUpdateLbcText()
{

    /* on s'occupe d'abord de l'essai pour prospect */
    header('Content-type: application/json');

    $retour = new \stdClass();
    $retour->error = false;
    $retour->message = 'ok';

    $fields = $_POST['fields'];
    $fields = json_decode(stripslashes($fields));
    $lbc_text = $fields->lbc_text;
    $ref_text = $fields->ref_text;

    $lbcTextMg = new \spamtonprof\stp_api\LbcTexteManager();
    $lbcTextMg->updateTexte(new \spamtonprof\stp_api\LbcTexte(array(
        "texte" => $lbc_text,
        "ref_texte" => $ref_text
    )));

    echo (json_encode($retour));

    die();
}
