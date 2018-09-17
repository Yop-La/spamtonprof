
<?php 



				/*
					Ce script sert à attribuer le tag paj (pas à jour) des élèves actifs. Il s'inscrit dans le processus de maj des comptes sur getresponse
					Il tourne plusieurs fois dans la nuit avant le script de maj des élèves
					hypo : la base de données est clean (on lui fait confiance)
					Le script attribue simplement le tag paj

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


					// pour récupérer tous les élèves actifs sans le tag paj
					$eleveActif = $getresponse->getContactsSearchContacts('qHdB');
					$eleveActif=json_decode(json_encode($eleveActif),true);
					$nb_mail = 0;


					foreach ($eleveActif as $contact){

						// if($nb_mail>=20){
						// 	break;
						// }
						// $nb_mail++;
						$tag_paj = 'ajn2';


						$params = '{
						    "tags": [
						        {
						            "tagId": "'.$tag_paj.'"
						        }
						    ]
						}';

						$params = json_decode($params);
						print_r($params);
						echo("<br>");
						// echo($mail_eleve.' '.$ref_compte.' '.$statut);
						echo("<br>");

						$res = $getresponse->addTags($contact['contactId'], $params);
						print_r($res);
						echo("<br><br> fin contact <br><br>");
						


					}


					$now = new DateTime(null,new \DateTimeZone("Europe/Paris"));
					to_log_slack(array("str1" => "cron exécuté le " .$now -> format('l jS \of F Y h:i:s A') ,"str2" => "taj paj élève gr ", ));



?>