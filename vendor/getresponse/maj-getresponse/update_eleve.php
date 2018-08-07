
<?php 


				/*
					Ce script sert à mettre à jour les contacts des élèves en essai et inscrit de getresponse
					Il tourne plusieurs fois dans la nuit
					hypo : la base de données est clean (on lui fait confiance)
					Le script écrase tous les autres champs (sauf l'adresse mail)
					Ce script met à jour ces champs
					- prenom
					- prenom proche
					- code_coupon
					

				*/


					require_once('/home/clients/yopyopla/prod/spamtonprof/init_perso.php' );
					require_once(get_home_path().'/getresponse/GetResponseAPI3.class.php' );
					require_once(get_home_path().'/getresponse/functions_gr.php' );

					$nb400Code = 0;

                    try
                    {
                        $bdd = new PDO('pgsql:host='.DB_HOST_PG.';port=5432;dbname='.DB_NAME_PG.';user='.DB_USER_PG.';password='.DB_PASSWORD_PG);

                    }
                    catch(Exception $e)
                    {
                            die('Erreur : '.$e->getMessage());
                    }
                    $getresponse = new GetResponse(GR_API);


					// ----------------------  calculer le nombre de jours d'inactivité --------------------------


					// pour récupérer tous les élèves actifs taggués paj
					$eleveActif = $getresponse->getContactsSearchContacts('qHRU');
					$eleveActif=json_decode(json_encode($eleveActif),true);
					$nb_mail = 0;


					foreach ($eleveActif as $contact){

						if($nb_mail>=100){
							break;
						}
						$nb_mail++;

						$req_eleve = $bdd->prepare("select parent.prenom as prenom_parent, coupon_code, statut, eleve.classe as classe_eleve, nb_message_last_week, nb_jour_inactivite, mail_relance,
							lower(eleve.adresse_mail) as mail_eleve, eleve.prenom as prenom_eleve
								from compte_eleve,eleve,parent 
								where compte_eleve.ref_eleve = eleve.ref_eleve 
									and compte_eleve.ref_parent = parent.ref_parent
									and statut in ('essai','inscrit')
									and same_email = false
									and lower(eleve.adresse_mail) like lower(?)");
						$req_eleve->execute(array($contact['email']));
						$eleve = $req_eleve -> fetch();

						$classe_eleve = $eleve['classe_eleve'];
						$prenom_eleve = $eleve['prenom_eleve'];
						$prenom_parent = $eleve['prenom_parent'];
						$coupon_code = $eleve['coupon_code'];
						$nb_message_last_week = $eleve['nb_message_last_week'];
						$nb_jour_inactivite = $eleve['nb_jour_inactivite'];
						$mail_relance = $eleve['mail_relance'];


						$req_tag_classe = $bdd->prepare("select ref_gr
								from tag_gr
								where nom_tag like ?");
						$req_tag_classe->execute(array($classe_eleve));
						$tag_classe = $req_tag_classe -> fetch();

						//custom field
						$prenom_proche_id = '3ytt8';
						$coupon_code_id = '3a93e';
						$nb_message_last_week_id = '35liC';
						$nb_jour_inactivite_id = '3LIAg';

						//tags
						$tag_classe = $tag_classe['ref_gr'];
						echo('tag classe : '.$tag_classe);
						$tag_maj = 'a506';
						$tag_nb_message = getTagNbMessage($nb_message_last_week);

						$tag_nb_jour_sans_rep ="";
						if($mail_relance){
							$tag_nb_jour_sans_rep = getTabNbJoursInactivite($nb_jour_inactivite);
						}


						$params = '{
						    "name": "'.$prenom_eleve.'",
						    "tags": [
						        {
						            "tagId": "'.$tag_classe.'"
						        },
						        {
						            "tagId": "'.$tag_maj.'"
						        },'.$tag_nb_jour_sans_rep.'
						        {
						            "tagId": "'.$tag_nb_message.'"
						        }

						    ],
						    "customFieldValues": [
						        {
						            "customFieldId": "'.$prenom_proche_id.'",
						            "value": [
						                "'.$prenom_parent.'"
						            ]
						        },
						        {
						            "customFieldId": "'.$coupon_code_id.'",
						            "value": [
						                "'.$coupon_code.'"
						            ]
						        },
						        {
						            "customFieldId": "'.$nb_message_last_week_id.'",
						            "value": [
						                "'.$nb_message_last_week.'"
						            ]
						        },
						        {
						            "customFieldId": "'.$nb_jour_inactivite_id.'",
						            "value": [
						                "'.$nb_jour_inactivite.'"
						            ]
						        }
						    ]
						}';

						$params = json_decode($params);
						print_r($params);
						echo("<br>");
						// echo($mail_eleve.' '.$ref_compte.' '.$statut);
						echo("<br>");

						$res = $getresponse->updateContact($contact['contactId'], $params);
						print_r($res);

						if($res -> httpStatus == "400"){
							$nb400Code++;
						}

						echo("<br><br> fin contact <br><br>");
						


					}


					$now = new DateTime(null,new \DateTimeZone("Europe/Paris"));

					to_log_slack(array("str1" => "cron exécuté le " .$now -> format('l jS \of F Y h:i:s A') ,"str2" => "maj comptes gr élèves ", "str3" => "nb code 400 : ".$nb400Code ));



?>