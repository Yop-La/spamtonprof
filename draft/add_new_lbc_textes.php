<?php

/*
 *
 * pour ajouter des nouveaux textes ( réponses auto leboncoin, textes d'annonces , etc )
 *
 */
define('PROBLEME_CLIENT', true);

$type_txt_str = "rep_amazon";

$textes_str = array(
    "Bonjour,
    
Désolé, ça vient tout juste de partir. C'est plus vite que je pensais.

Si tu veux, je l'ai acheté ici: [lien_affilie]. Il en reste peut être encore.

Comme ça, tu l'auras en neuf et à pas cher.

Allez, bonne journée :)

[prof_name]
",    "Désole, je viens de confirmer la vente à qq d'autres. 

Mais t'inquiète pas, tu retrouver ça sur ce site : [lien_affilie] 

Il en reste peut être encore.

Tu connais peut être. C'est amazon. Il y a des bons produits neufs et à pas cher. 

Je trouve que c'est un bon plan !

Bonne journée :)

[prof_name]

");

$typeTxtMg = new \spamtonprof\stp_api\TypeTexteManager();

$textMg = new \spamtonprof\stp_api\LbcTexteManager();

// on doit d'abord ajouter le type

$type_txt = $typeTxtMg->get(array(
    'type' => $type_txt_str
));

if (! $type_txt) {

    $type_txt = $typeTxtMg->add(new \spamtonprof\stp_api\TypeTexte(array(
        "type" => $type_txt_str
    )));
}

// on ajoute ensuite les textes

foreach ($textes_str as $texte_str) {

    $textMg->add(new \spamtonprof\stp_api\LbcTexte(array(
        "texte" => $texte_str,
        "type" => $type_txt->getType(),
        "ref_type_texte" => $type_txt->getRef_type()
    )));
}

