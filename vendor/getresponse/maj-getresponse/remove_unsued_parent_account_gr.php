<?php 
		

				/*
					Ce script sert à retirer de getresponse les adresses mails des parents en essai et inscrit qui sont plus en essai ou inscrit dans la bdd
					Il est activé par un cron tous les jours
					hypo : la base de données est clean (on lui fait confiance)

				*/



					require_once('/home/clients/yopyopla/prod/spamtonprof/init_perso.php' );
					require_once(get_home_path().'/getresponse/GetResponseAPI3.class.php' );
					require_once(get_home_path().'/getresponse/functions_gr.php' );
					
                    try
                    {
                        $bdd = new PDO('pgsql:host='.DB_HOST_PG.';port=5432;dbname='.DB_NAME_PG.';user='.DB_USER_PG.';password='.DB_PASSWORD_PG);

                    }
                    catch(Exception $e)
                    {
                            die('Erreur : '.$e->getMessage());
                    }
                    $getresponse = new GetResponse(GR_API);


					$req_parent = $bdd->prepare("select lower(parent.adresse_mail) as mail_parent
							from compte_eleve,eleve,parent 
							where compte_eleve.ref_eleve = eleve.ref_eleve 
								and compte_eleve.ref_parent = parent.ref_parent
								and statut in ('essai','inscrit')");
					$req_parent->execute();

					$parentEleveInBdd = [];
					$nb_mail_in_Bdd = 0;

					while($parent = $req_parent->fetch()){
						$mailParentInBdd[$nb_mail_in_Bdd] =  strtolower($parent['mail_parent']);
						$nb_mail_in_Bdd++;

					}
					// print_r($mailParentInBdd);


					// première partie : on vire les contacts n'ayant qu'un seul compte

					// pour récupérer tous les parent  en essai ou inscrit dans getresponse avec un seul compte
					$parentActif = $getresponse->getContactsSearchContacts('qYaG');
					$parentActif=json_decode(json_encode($parentActif),true);
					foreach ($parentActif as $contact){
						// echo($contact['email']);
						if(!in_array(strtolower($contact['email']), $mailParentInBdd)){ //si contact getresponse pas dans bdd
							print_r($contact['email']);

							$params = '{
							    "campaign": {
							    	"campaignId": "47CmU"
							    }
							}';
							$params = json_decode($params);
							print_r($getresponse->updateContact($contact['contactId'],$params));
						}
					}			


					// première partie : on vire les contacts ayant deux comptes

					// pour récupérer tous les parent  en essai ou inscrit dans getresponse avec deux comptes
					$parentActif = $getresponse->getContactsSearchContacts('qY4h');
					$parentActif=json_decode(json_encode($parentActif),true);
					foreach ($parentActif as $contact){
						// echo($contact['email']);
						if(!in_array(strtolower($contact['email']), $mailParentInBdd)){ //si contact getresponse pas dans bdd
							print_r($contact['email']);


							$req_parent_2_compte = $bdd->prepare("select count(*) as nb_account
									from compte_eleve,eleve,parent 
									where compte_eleve.ref_eleve = eleve.ref_eleve 
										and compte_eleve.ref_parent = parent.ref_parent
										and statut in ('essai','inscrit')
										and lower(parent.adresse_mail) like lower(?)");
							$req_parent_2_compte->execute(array($contact['email']));
							$nb_account = $req_parent_2_compte->fetch();
							$nb_account = $nb_account['nb_account'];

							if($nb_account == 0){
								$params = '{
								    "campaign": {
								    	"campaignId": "47CmU"
								    }
								}';
								$params = json_decode($params);
								print_r($getresponse->updateContact($contact['contactId'],$params));								
							}
						}
					}			
					echo("<br><br> Done <br><br>");


?>