<?php
/*
 * ce script sert à sauvegarder les comptes à facturer pour la facturation manuelle
 * cf fonction generateInvoicesCsv de facture manager
 */
require_once (dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-config.php");
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// on supprime tous les fichiers objet du dossier tempo/invoice
$files = glob('../tempo/invoice/*'); 
foreach($files as $file){ 
    if(is_file($file))
        unlink($file); 
}

$accountMg = new \spamtonprof\stp_api\AccountManager();

$refComptes = $accountMg->getAllRefCompte(array(
    "essai",
    "inscrit"
), null, true, false);

$arraysRefComptes = array_chunk ($refComptes, 50);

$url = plugins_url("spamtonprof/invoice/AccountSaver.php");

echo($url."<br>");

foreach ($arraysRefComptes as $arrayRefComptes){
    
    call($url,"GET",array("refComptes" => $arrayRefComptes),true);
    
    print_r($arrayRefComptes);
    
    echo("<br><br>");
    
    
    
}
