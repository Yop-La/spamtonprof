<?php

/*
 *
 * pour ajouter des nouveaux textes ( réponses auto leboncoin, textes d'annonces , etc )
 *
 */
define('PROBLEME_CLIENT', true);

$type_txt_str = "rep_get_phone";

$textes_str = array(
    "Bonjour,
    
Quel est votre numéro de téléphone ? Je peux vous appeler vers 18h après les cours.
    
Cela vous convient ? Sinon, donnez moi vos disponibilités pour qu'on en discute ensemble.
    
Je vous remercie,
    
À bientôt,
    
[prof_name]
"
);

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

