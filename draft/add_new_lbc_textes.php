<?php

/*
 *
 * pour ajouter des nouveaux textes ( réponses auto leboncoin, textes d'annonces , etc )
 *
 */
define('PROBLEME_CLIENT', true);

$type_txt_str = "rep_vetement";

$textes_str = array(
    "Bonjour,
    
Désolé, il vient tout juste de partir. Ça part vite.

Je l'ai acheté sur ce site : 

C'est proposé par les fabriquants directement.

Du coup, c'est pas cher !! 

J'ai trouvé des affaires zara à 20-30 € moins cher. Et c'est la même qualité.

Un sacré bon plan.

Allez, bonne journée :)

[prof_name]
",    "Désole, je viens de confirmer la vente à qq d'autres. Mais t'inquiète pas, tu peux vite retrouver cette article sur ce site : 

Tu connais peut être. Il y a des fringues que tu peux trouvez, en ce moment, chez Zara par exemple. 

Avec la même qualité mais c'est 70% moins chères. ( comme ça vient du fabricant directement ). 

Quand j'ai découvert ce site, j'ai direcement commandé 6 tenues pour presque rien du tout. 

Voilà mon bon plan !

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

