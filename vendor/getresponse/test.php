<?php 
		

				/*
					Ce script sert à tester l'api de getresponse
				*/



					// include("functions_gr.php");
					include("GetResponseAPI3.class.php");
					ini_set('display_errors', 1);
					ini_set('display_startup_errors', 1);
					error_reporting(E_ALL);
					require_once('/home/clients/yopyopla/prod/spamtonprof/wp-load.php' );


					function chargerClasseStp($classname)
					{	
					  include_once(get_stylesheet_directory().'/stp_api/'.$classname.'.php');
					}

					spl_autoload_register('chargerClasseStp');

					
                    try
                    {
                        $bdd = new PDO('pgsql:host='.DB_HOST_PG.';port=5432;dbname='.DB_NAME_PG.';user='.DB_USER_PG.';password='.DB_PASSWORD_PG);

                    }
                    catch(Exception $e)
                    {
                            die('Erreur : '.$e->getMessage());
                    }




                    $accountManager = new AccountManager($bdd);
                    $account = $accountManager->get(851);

                    // $account->setAttente_paiement(false);
                    // $account->setStatut("inscrit");
                    // $accountManager->updateAfterSubsCreated($account);


                    $email_eleve = $account->eleve()->adresse_mail();
                    $email_parent = $account->proche()->adresse_mail();
                    $prenom_eleve = $account->eleve()->prenom();
                    $prenom_parent = $account->proche()->prenom();

                    // faire les changements de liste
                    $getresponse = new GetResponse(GR_API);

                    //déterminer les ref de campagne
                    $campaignIdProche; 
                    $campaignIdEleve;
                    $campaignIdEleveOld;
                    $campaignIdProcheOld;
                    if($account ->francais()){
                    	$campaignIdProche = "4b4hs";
                    	$campaignIdEleve = "4b4vi";
                    	$campaignIdProcheOld = "4t7kQ";
                    	$campaignIdEleveOld = "4t7ut";
                    }else if($account ->maths() or $account ->physique()){
                    	$campaignIdProche = "45XJl";
                    	$campaignIdEleve = "45X2f";
                    	$campaignIdProcheOld = "4TPZW";
                    	$campaignIdEleveOld = "4TP5I";
                    }

                    // // supprimer les doublons d'emails élèves
                    // echo("begin"."<br>");
                    // echo($email_parent."<br>");
                    // echo($campaignIdProcheOld."<br>");

                    // $contact_eleve;

                    // $contacts = $getresponse->getContacts(array("query[email]"=>$email_parent,"query[campaignId]"=>$campaignIdProcheOld));
                    // $number_contact = count((array)$contacts);
                    // echo("number contact : ".$number_contact);

                    $contact_parent;
                    $contacts = $getresponse->getContacts(array("query[email]"=>$email_parent,"query[campaignId]"=>$campaignIdProcheOld));
                    $number_contact = count((array)$contacts);
                    if($number_contact > 1){ //pour supprimer les contacts doublons
                    	$i = 0;
                    	foreach ($contacts as $contact) {
                    		if(++$i === $number_contact) {
                    			$contact_parent = $contact;
                    		}else{
                    			$ret =$getresponse->deleteContact($contact->contactId);
                    		}
                    	}
                    }else if($number_contact == 1){
                    	$contacts = (array)$contacts;
                    	$contact_parent = $contacts[0];	
                    	// changement de campagne parent
                    	$params = '{
                    	    "campaign": {
                    	    	"campaignId": "'.$campaignIdProche.'"
                    	    },
                    	    "dayOfCycle": "0"
                    	}';
                    	$params = json_decode($params);
                    	$res = $getresponse->updateContact($contact_parent->contactId, $params);

                    	// update du contact proche
                    	$params = '{
                    	    "name": "'.$prenom_parent.'",
                    	    "customFieldValues": [
                    	        {
                    	            "customFieldId": "3ytt8",
                    	            "value": [
                    	                "'.$prenom_eleve.'"
                    	            ]
                    	        }
                    	    ]
                    	}';
                    	$params = json_decode($params);
                    	$res = $getresponse->updateContact($contact_parent->contactId, $params);

                    }else if($number_contact == 0){
                    	// update du contact proche
                    	$params = '{
                    	    "name": "'.$prenom_parent.'",
                    	    "email": "'.$email_parent.'",
                    	    "campaign": {
                    	    	"campaignId": "'.$campaignIdProche.'"
                    	    },
                    	    "dayOfCycle": "0",
                    	    "customFieldValues": [
                    	        {
                    	            "customFieldId": "3ytt8",
                    	            "value": [
                    	                "'.$prenom_eleve.'"
                    	            ]
                    	        }
                    	    ]
                    	}';
                    	$params = json_decode($params);
                    	$res = $getresponse->addContact( $params);

                    }

?>