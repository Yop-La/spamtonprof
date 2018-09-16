<?php 




				/*
					Ce script sert à mettre à jour les contacts - parents en essai et inscrit de getresponse
					Il tourne plusieurs fois dans la nuit
					hypo : la base de données est clean (on lui fait confiance)
					Le script écrase tous champs (sauf l'adresse mail)
					Ce script met à jour ces champs
					- prenom
					- prenom proche
					- code_coupon
					- prenom proche 2 (si un contact a deux comptes)
					- prenom proche 2 (si un contact a deux comptes)
					

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


					// pour récupérer tous les parents actifs taggués paj
					$parentActif = $getresponse->getContactsSearchContacts('qHEu');
					$parentActif=json_decode(json_encode($parentActif),true);
					$nb_mail = 0;

					// pour éviter de mettre à jour deux fois une adresse mail parent avec deux comptes
					$mailParentInserted= [];
					$nb_mail_2comptes=0;


					foreach ($parentActif as $contact){

						if($nb_mail>=100){
							break;
						}
						$nb_mail++;

						$mail_parent = $contact['email'];

						$req_parent = $bdd->prepare("select parent.prenom as prenom_parent, coupon_code, statut, eleve.classe as classe_eleve, compte_eleve.ref_parent as refe_parent, nb_message_last_week, 
							lower(eleve.adresse_mail) as mail_eleve, eleve.prenom as prenom_eleve, compte_associe, compte_eleve.ref_eleve as refe_eleve, nb_jour_inactivite, mail_relance, same_email
								from compte_eleve,eleve,parent 
								where compte_eleve.ref_eleve = eleve.ref_eleve 
									and compte_eleve.ref_parent = parent.ref_parent
									and statut in ('essai','inscrit')
									and lower(parent.adresse_mail) like lower(?)
								order by date_creation desc");
						$req_parent->execute(array($mail_parent));
						$parent = $req_parent -> fetch();

						$classe_eleve = $parent['classe_eleve'];
						$same_email = $parent['same_email'];
						$prenom_eleve = $parent['prenom_eleve'];
						$prenom_parent = $parent['prenom_parent'];
						$coupon_code = $parent['coupon_code'];
						$compte_associe = $parent['compte_associe'];
						$ref_parent = $parent['refe_parent'];
						$ref_eleve = $parent['refe_eleve'];
						$nb_message_last_week = $parent['nb_message_last_week'];
						$nb_jour_inactivite = $parent['nb_jour_inactivite'];
						$mail_relance = $parent['mail_relance'];

						if($compte_associe == 1){

							$req_tag_classe = $bdd->prepare("select ref_gr
									from tag_gr
									where nom_tag like ?");
							$req_tag_classe->execute(array($classe_eleve));
							$tag_classe = $req_tag_classe -> fetch();

							//custom fields
							$prenom_proche_id = '3ytt8';
							$coupon_code_id = '3a93e';
							$nb_jour_inactivite_id = '3LIAg';
							$nb_message_last_week_id = '35liC';

							//tags
							$tag_classe = $tag_classe['ref_gr'];
							$tag_maj = 'a506';
							$tag_un_compte = 'aj4M';
							$tag_id_same_email = 'aEM8';
							$tag_nb_message = getTagNbMessage($nb_message_last_week);
							
							$tag_nb_jour_sans_rep ="";
							if($mail_relance){
								$tag_nb_jour_sans_rep = getTabNbJoursInactivite($nb_jour_inactivite);
							}
							
							$tag_same_email ="";
							if($same_email){
								$tag_same_email = 
									'{
							            "tagId": "'.$tag_id_same_email.'"
							        },' ;
							}


							$params = '{
							    "name": "'.$prenom_parent.'",
							    "tags": [
							        {
							            "tagId": "'.$tag_classe.'"
							        },
							        {
							            "tagId": "'.$tag_maj.'"
							        },
							        {
							            "tagId": "'.$tag_un_compte.'"
							        },'.$tag_nb_jour_sans_rep.$tag_same_email.'
							        {
							            "tagId": "'.$tag_nb_message.'"
							        }

							    ],
							    "customFieldValues": [
							        {
							            "customFieldId": "'.$prenom_proche_id.'",
							            "value": [
							                "'.$prenom_eleve.'"
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
							echo("<br><br> fin contact <br><br>");
						}else{ // sous entendu :compte associe== 2 )
							if(!in_array($mail_parent, $mailParentInserted)){
								$req_tag_classe = $bdd->prepare("select ref_gr
										from tag_gr
										where nom_tag like ?");
								$req_tag_classe->execute(array($classe_eleve));
								$tag_classe = $req_tag_classe -> fetch();

								// custom fields
								$prenom_proche_id = '3ytt8';
								$coupon_code_id = '3a93e';
								$prenom_proche_id_2 = '3zwiQ';
								$coupon_code_id_2 = '3zwLh';
								$nb_message_last_week_id_2 = '353jB';
								$nb_jour_inactivite_id_2 = '3LIhB';
								$nb_jour_inactivite_id = '3LIAg';
								$nb_message_last_week_id = '35liC';


								// tags
								$tag_classe = $tag_classe['ref_gr'];
								$tag_maj = 'a506';
								$tag_deux_comptes = 'a5mG';
								$tag_nb_message = getTagNbMessage($nb_message_last_week);

								// on récupère le deuxième compte
								$req_second_account = $bdd->prepare("select coupon_code, eleve.prenom as prenom_eleve, classe, nb_message_last_week, nb_jour_inactivite, mail_relance,same_email
									from compte_eleve,eleve,parent 
										where compte_eleve.ref_parent = parent.ref_parent 
											and compte_eleve.ref_eleve = eleve.ref_eleve
											and statut in ('essai','inscrit')
											and compte_eleve.ref_parent = ?  and eleve.ref_eleve != ?");
								$req_second_account->execute(array($ref_parent,$ref_eleve));
								$second_account = $req_second_account->fetch();
								$prenom_eleve_2 = $second_account['prenom_eleve'];
								$coupon_code_2 = $second_account['coupon_code'];
								$classe_eleve_2 = $second_account['classe'];
								$nb_message_last_week_2 = $second_account['nb_message_last_week'];
								$nb_jour_inactivite_2 = $second_account['nb_jour_inactivite'];
								$mail_relance_2 = $parent['mail_relance'];
								$same_email_2 = $parent['same_email'];


								$req_tag_classe_2 = $bdd->prepare("select ref_gr
										from tag_gr
										where nom_tag like ?");
								$req_tag_classe_2->execute(array($classe_eleve_2));
								$tag_classe_2 = $req_tag_classe_2 -> fetch();
								$tag_classe_2 = $tag_classe_2['ref_gr'];
								$tag_nb_message_2 = getTagNbMessage_2($nb_message_last_week_2);
								$tag_nb_jour_sans_rep ="";
								$tag_id_same_email = 'aEM8';

								if($mail_relance){
									$tag_nb_jour_sans_rep = getTabNbJoursInactivite($nb_jour_inactivite);
									
								}
								$tag_nb_jour_sans_rep_2="";
								if($$mail_relance_2){
									$tag_nb_jour_sans_rep_2 = getTabNbJoursInactivite_2($nb_jour_inactivite_2);
								}

								$tag_same_email ="";
								if($same_email_2 | $same_email){
									$tag_same_email = 
										'{
								            "tagId": "'.$tag_id_same_email.'"
								        },';
								}

								$params = '{
								    "name": "'.$prenom_parent.'",
								    "tags": [
								        {
								            "tagId": "'.$tag_classe.'"
								        },
								        {
								            "tagId": "'.$tag_classe_2.'"
								        },
								        {
								            "tagId": "'.$tag_maj.'"
								        },
								        {
								            "tagId": "'.$tag_deux_comptes.'"
								        },'.$tag_nb_jour_sans_rep.$tag_nb_jour_sans_rep_2.$tag_same_email.'
								        {
								            "tagId": "'.$tag_nb_message.'"
								        },
								        {
								            "tagId": "'.$tag_nb_message_2.'"
								        }

								    ],
								    "customFieldValues": [
								        {
								            "customFieldId": "'.$prenom_proche_id.'",
								            "value": [
								                "'.$prenom_eleve.'"
								            ]
								        },
								        {
								            "customFieldId": "'.$coupon_code_id.'",
								            "value": [
								                "'.$coupon_code.'"
								            ]
								        },
								        {
								            "customFieldId": "'.$prenom_proche_id_2.'",
								            "value": [
								                "'.$prenom_eleve_2.'"
								            ]
								        },
								        {
								            "customFieldId": "'.$coupon_code_id_2.'",
								            "value": [
								                "'.$coupon_code_2.'"
								            ]
								        },
    							        {
    							            "customFieldId": "'.$nb_message_last_week_id.'",
    							            "value": [
    							                "'.$nb_message_last_week.'"
    							            ]
    							        },
								        {
								            "customFieldId": "'.$nb_message_last_week_id_2.'",
								            "value": [
								                "'.$nb_message_last_week_2.'"
								            ]
								        },
								        {
								            "customFieldId": "'.$nb_jour_inactivite_id.'",
								            "value": [
								                "'.$nb_jour_inactivite.'"
								            ]
								        },
								        {
								            "customFieldId": "'.$nb_jour_inactivite_id_2.'",
								            "value": [
								                "'.$nb_jour_inactivite_2.'"
								            ]
								        }
								    ]
								}';
								echo($params."<br><br>");

								$params = json_decode($params);
								print_r($params);
								echo("<br>");
								// echo($mail_eleve.' '.$ref_compte.' '.$statut);
								echo("<br>");

								$res = $getresponse->updateContact($contact['contactId'], $params);

								if($res -> httpStatus == "400"){
									$nb400Code++;
								}

								print_r($res);
								echo("<br><br> fin contact <br><br>");
								$mailParentInserted[$nb_mail_2comptes] = $mail_parent;
								$nb_mail_2comptes++;
							}
						}

					}


					$now = new DateTime(null,new \DateTimeZone("Europe/Paris"));

					to_log_slack(array("str1" => "cron exécuté le " .$now -> format('l jS \of F Y h:i:s A') ,"str2" => "maj comptes gr parents", "str3" => "nb code 400 : ".$nb400Code ));



?>