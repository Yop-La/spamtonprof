<?php 
		

	/*
		Ce script sert à tester l'api de getresponse
	*/

	include("GetResponseAPI3.class.php");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	require_once('/home/clients/yopyopla/prod/spamtonprof/wp-load.php' );
	
    try
    {
        $bdd = new PDO('pgsql:host='.DB_HOST_PG.';port=5432;dbname='.DB_NAME_PG.';user='.DB_USER_PG.';password='.DB_PASSWORD_PG);

    }
    catch(Exception $e)
    {
            die('Erreur : '.$e->getMessage());
    }
    $getresponse = new GetResponse(GR_API);

	// pour récupérer l'id d'un custom field
	$customFields = $getresponse->getCustomFields();
	$customFields=json_decode(json_encode($customFields),true);
	echo($customFields);
	print_r($customFields);
	
	echo("<br><br><br><br><br>");

	// pour récupérer les recherches sauvegardés
	$contactsSansCustomField = $getresponse -> getContactsSearch('');
	$contactsSansCustomField=json_decode(json_encode($contactsSansCustomField),true);
	echo($contactsSansCustomField);
	print_r($contactsSansCustomField);

	// pour récupérer les ids de campagne
	$campaigns = $getresponse -> getCampaigns();
	$campaigns=json_decode(json_encode($campaigns),true);
	echo("<br><br><br><br><br>");
	print_r($campaigns);

    // pour récupérrer les id d'expé
    $expediteurs = $getresponse ->getFromFields();
    $expediteurs=json_decode(json_encode($expediteurs),true);
    print_r($expediteurs);

    // --------------- pour récupérer tous les tags ------------
    $tags = $getresponse->getTags();
    $tags = json_decode(json_encode($tags));
    print_r($tags);

?>