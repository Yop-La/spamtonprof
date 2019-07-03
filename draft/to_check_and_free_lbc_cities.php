<?php

/*
 *
 * pour voir les communes qui vont être utilisés lors de la prochaine publication
 * pour libérer des communes à potentiel et les utiliser lors de la prochaine publication
 *
 */

$refClient = 25;

// on recupere les communes
$communeMg = new \spamtonprof\stp_api\LbcCommuneManager();

$communes = $communeMg->getAll(array(
    "ref_client" => $refClient
));

prettyPrint($communes);



$addTempoMg = new \spamtonprof\stp_api\AddsTempoManager();
$addTempoMg->deleteAll(array("high_potential_city" => $refClient));
exit(0);

