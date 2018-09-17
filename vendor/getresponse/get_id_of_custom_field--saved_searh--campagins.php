<?php


	require_once('/home/clients/yopyopla/prod/spamtonprof/init_perso.php' );
	require_once(get_home_path().'/getresponse/GetResponseAPI3.class.php' );
	require_once(get_home_path().'/getresponse/functions_gr.php' );

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