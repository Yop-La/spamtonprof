<?php
use spamtonprof\stp_api;

use spamtonprof\stp_api\CampaignManager;
use spamtonprof\stp_api\Abonnement;
use spamtonprof\stp_api\AbonnementManager;

// toutes ces fonction seront éxécutés par un appel ajax réalisé dans adds-back-office.js sur la page dont le slug est adds
add_action('wp_ajax_ajaxGetAddsTitle', 'ajaxGetAddsTitle');

add_action('wp_ajax_nopriv_ajaxGetAddsTitle', 'ajaxGetAddsTitle');

add_action('wp_ajax_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_nopriv_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_nopriv_ajaxGetTitles', 'ajaxGetTitles');

add_action('wp_ajax_ajaxGetAddsTexteType', 'ajaxGetAddsTexteType');

add_action('wp_ajax_nopriv_ajaxGetAddsTexteType', 'ajaxGetAddsTexteType');

add_action('wp_ajax_ajaxGetTextes', 'ajaxGetTextes');

add_action('wp_ajax_nopriv_ajaxGetTextes', 'ajaxGetTextes');

add_action('wp_ajax_ajaxAddNewTexteCat', 'ajaxAddNewTexteCat');

add_action('wp_ajax_nopriv_ajaxAddNewTexteCat', 'ajaxAddNewTexteCat');

add_action('wp_ajax_ajaxGetTexteCat', 'ajaxGetTexteCat');

add_action('wp_ajax_nopriv_ajaxGetTexteCat', 'ajaxGetTexteCat');

add_action('wp_ajax_ajaxGetTexteCats', 'ajaxGetTexteCats');

add_action('wp_ajax_nopriv_ajaxGetTexteCats', 'ajaxGetTexteCats');

add_action('wp_ajax_ajaxAddLbcParas', 'ajaxAddLbcParas');

add_action('wp_ajax_nopriv_ajaxAddLbcParas', 'ajaxAddLbcParas');

add_action('wp_ajax_ajaxGetTexts', 'ajaxGetTexts');

add_action('wp_ajax_nopriv_ajaxGetTexts', 'ajaxGetTexts');

add_action('wp_ajax_ajaxUpdateLbcParas', 'ajaxUpdateLbcParas');

add_action('wp_ajax_nopriv_ajaxUpdateLbcParas', 'ajaxUpdateLbcParas');

add_action('wp_ajax_ajaxDeleteTexte', 'ajaxDeleteTexte');

add_action('wp_ajax_nopriv_ajaxDeleteTexte', 'ajaxDeleteTexte');

add_action('wp_ajax_ajaxCountTexts', 'ajaxCountTexts');

add_action('wp_ajax_nopriv_ajaxCountTexts', 'ajaxCountTexts');

add_action('wp_ajax_ajaxGenerateAndSaveTexts', 'ajaxGenerateAndSaveTexts');

add_action('wp_ajax_nopriv_ajaxGenerateAndSaveTexts', 'ajaxGenerateAndSaveTexts');

/* retourne la liste des types de textes des annonces lbc */
function ajaxGetAddsTexteType()

{
    header('Content-type: application/json');
    
    $lbcTexteMg = new \spamtonprof\stp_api\LbcTexteManager();
    
    echo (json_encode($lbcTexteMg->getAllType()));
    
    die();
}

/* retourne la liste des types de titres des annonces lbc */
function ajaxGetAddsTitle()

{
    header('Content-type: application/json');
    
    $accountManager = new \spamtonprof\stp_api\LbcTitleManager();
    
    echo (json_encode($accountManager->getAllType()));
    
    die();
}

function ajaxGetTitles()

{
    header('Content-type: application/json');
    
    $typeTitle = $_POST["typeTitle"];
    
    $accountManager = new \spamtonprof\stp_api\LbcTitleManager();
    
    $titles = $accountManager->getAll($typeTitle);
    
    $csvName = "titles.csv";
    
    saveArrayAsCsv($titles, $csvName);
    
    echo (json_encode(array(
        "titles" => $titles,
        "csvPath" => plugins_url("spamtonprof/tempo/" . $csvName)
    )));
    
    die();
}

function ajaxGetTextes()

{
    header('Content-type: application/json');
    
    $typeTexte = $_POST["typeTexte"];
    
    $texteMg = new \spamtonprof\stp_api\LbcTexteManager();
    
    $textes = $texteMg->getAll($typeTexte);
    
    $csvName = "titles.csv";
    
    saveArrayAsCsv($textes, $csvName);
    
    echo (json_encode(array(
        "textes" => $textes,
        "csvPath" => plugins_url("spamtonprof/tempo/" . $csvName)
    )));
    
    die();
}

function ajaxAddNewTexteCat()

{
    header('Content-type: application/json');
    
    $nomCat = $_POST["nomCat"];
    $nbTexte = $_POST["nbTexte"];
    $nbPara = $_POST["nbPara"];
    
    $texteCat = new \spamtonprof\stp_api\LbcTexteCat(array(
        "nom_cat" => $nomCat,
        "nb_paragraph" => $nbPara,
        "nb_texte" => $nbTexte
    ));
    
    $texteCatMg = new \spamtonprof\stp_api\LbcTexteCatMg();
    
    $texteCatMg->add($texteCat);
    
    echo (json_encode($texteCat));
    
    die();
}

function ajaxGetTexteCat()

{
    header('Content-type: application/json');
    
    $nomCat = $_POST["nomCat"];
    
    $texteCatMg = new \spamtonprof\stp_api\LbcTexteCatMg();
    
    $texteCat = $texteCatMg->get(array(
        "nom_cat" => $nomCat
    ));
    
    echo (json_encode($texteCat));
    
    die();
}

function ajaxGetTexteCats()

{
    header('Content-type: application/json');
    
    $nomCat = $_POST["nomCat"];
    
    $texteCatMg = new \spamtonprof\stp_api\LbcTexteCatMg();
    
    $texteCat = $texteCatMg->getAll();
    
    echo (json_encode($texteCat));
    
    die();
}

function ajaxAddLbcParas()

{
 
    header('Content-type: application/json');
    
    $paragraphs = $_POST['paragraphs'];
    $refTextCat =  $_POST['refTexteCat'];
    
    $paragraphsMg = new \spamtonprof\stp_api\LbcParagraphMg();
    
    $lbcparagraphs = [];
    
    $textMg = new \spamtonprof\stp_api\LbcBaseTextMg();
    
    $text = new \spamtonprof\stp_api\LbcBaseText(array("ref_text_cat" => $refTextCat));
    
    $text = $textMg -> add($text);
    
    foreach ($paragraphs as $paragraph) {
        
        $lbcparagraph = new \spamtonprof\stp_api\LbcParagraph(array(
            "ref_texte" => $text->getRef_text(),
            "position" => $paragraph['indice'],
            "paragraph" => wp_unslash( $paragraph['paragraph'])
        ));
        
        $lbcparagraph = $paragraphsMg->add($lbcparagraph);
        $lbcparagraphs[] = $lbcparagraph;
    }
    
    echo (json_encode($lbcparagraphs));
    
    die();
}

function ajaxGetTexts(){
    
    header('Content-type: application/json');
    
    $refTextCat = $_POST['refTexteCat'];
    
    $textsMg = new \spamtonprof\stp_api\LbcBaseTextMg();
    
    
    $textes = $textsMg -> getTextsByParagraphs(array("ref_text_cat" => $refTextCat));
    
    echo (json_encode($textes));
    
    die();
    
}

function ajaxUpdateLbcParas(){
    
    header('Content-type: application/json');
    
    $paras = $_POST['paragraphs'];
    $texteId = $_POST['refTexte'];
    
    $paraMg = new \spamtonprof\stp_api\LbcParagraphMg();
    
    $oldParas = $paraMg->getAll(array('ref_text' => $texteId));
    
    foreach ($oldParas as $oldPara){
         
        $para = $paras[$oldPara->getPosition()];
        
        $oldPara -> setParagraph(wp_unslash($para['paragraph']));
        
        $paraMg -> updateParagraph($oldPara);
        
    }
    
    
    
    echo (json_encode($oldParas));
    
    die();
       
}

function ajaxDeleteTexte(){
    
    header('Content-type: application/json');
    
    $refTexte = $_POST['refTexte'];
    
    $texteMg = new \spamtonprof\stp_api\LbcBaseTextMg();
    
    $texteMg -> delete($refTexte);
    
    echo (json_encode($refTexte));
    
    die();
    
}

function ajaxCountTexts(){

    
    header('Content-type: application/json');
    
    $nomCatLoaded = $_POST['nomCatLoaded'];
    
    $texteMg = new \spamtonprof\stp_api\LbcBaseTextMg();
    
    $nbText = $texteMg -> count($nomCatLoaded);
    
    echo (json_encode($nbText));
    
    die();
    
}

function ajaxGenerateAndSaveTexts(){
    
    
    header('Content-type: application/json');
    
    $nomCatLoaded = $_POST['nomCatLoaded'];
    
    $lbcTexteCatMg = new \spamtonprof\stp_api\LbcTexteCatMg();
    
    $lbcTexteCat = $lbcTexteCatMg -> get(array("nom_cat" => $nomCatLoaded));
    
    $lbcTexteGenerator = new \spamtonprof\stp_api\LbcTexteGenerator($lbcTexteCat);
    
    $textesGenerated = $lbcTexteGenerator->generateTexts();
    
    $texteMg = new \spamtonprof\stp_api\LbcTexteManager();
    
    $texteMg->deleteAll(array("type" => $nomCatLoaded));
    
    $textes = [];
    
    foreach ($textesGenerated as $texteGenerated){
        
        $texte = new \spamtonprof\stp_api\LbcTexte(array("type" => $nomCatLoaded, "texte" => $texteGenerated));
        
        $texte = $texteMg -> add($texte);
        
        $textes[] = $texte;
    }
    
    echo (json_encode($textes));
    
    die();
    
}



