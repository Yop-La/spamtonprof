<?php 
		

				/*
					Ce script sert à insérer les adresses mails des parents en essai et inscrit qui sont dans la bdd mais pas dans getresponse
					Il tourne une fois par jour
					hypo : la base de données est clean (on lui fait confiance)
					Ce script insère les champs suivant pour chaque parent :
					- prenom
					- mail
					- campagne
					- jour du cycle
					- prenom proche
					- coupon code
					- prenom proche_2 si le parent a deux comptes
					- coupon code_2  si le parent a deux comptes

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

                    $nb400Code = 0 ;

					// pour récupérer tous les parents sans prenom de proche
					$parentActif = $getresponse->getContactsSearchContacts('qHZ6');
					$parentActif=json_decode(json_encode($parentActif),true);
					$mailparentInGR = [];
					$nb_mail = 0;


					foreach ($parentActif as $contact){
						$mailparentInGR[$nb_mail] =  strtolower($contact['email']);
						$nb_mail++;
					}

					// pour éviter d'insérer deux fois une adresse mail parent avec deux comptes
					$mailParentInserted= [];
					$nb_mail=0;


					$req_parent = $bdd->prepare("select parent.prenom as prenom_parent, coupon_code, statut, compte_eleve.ref_compte as refe_compte, compte_associe, eleve.ref_eleve as refe_eleve, parent.ref_parent as refe_parent, lower(parent.adresse_mail) as mail_parent, eleve.prenom as prenom_eleve, EXTRACT(day FROM current_timestamp - date_creation  ) as dayofcycle
							from compte_eleve,eleve,parent 
							where compte_eleve.ref_parent = parent.ref_parent 
								and compte_eleve.ref_eleve = eleve.ref_eleve
								and statut in ('essai','inscrit')
								and (maths = true or physique = true)");
					$req_parent->execute();

					while($parent = $req_parent->fetch()){

						$prenom_parent = $parent['prenom_parent'];
						$mail_parent = $parent['mail_parent'];
						$prenom_eleve = $parent['prenom_eleve'];
						$coupon_code = $parent['coupon_code'];
						$statut = $parent['statut'];
						$ref_compte = $parent['refe_compte'];
						$ref_eleve = $parent['refe_eleve'];
						$ref_parent = $parent['refe_parent'];
						$dayOfCycle =$parent['dayofcycle'];
						$compte_associe =$parent['compte_associe'];
						// echo($parent['dayOfCycle']);
						if(!in_array($mail_parent, $mailparentInGR)){ //si pas dans getresponse
							echo('$compte_associe : '.$compte_associe);
							if($compte_associe == 1){
								echo("debut contact<br><br>");
								// echo($mail_parent);
								// print_r($parent);
								// echo($dayOfCycle."<br><br>");
								if($statut == 'essai'){
									$dayOfCycle = intval($dayOfCycle);
									$campaignId = '4TPZW';
								}else{
									$dayOfCycle = intval($dayOfCycle)-10;
									$campaignId = '45XJl';

								}
								$prenom_proche_id = '3ytt8';
								$coupon_code_id = '3a93e';

								$params = '{
								    "name": "'.$prenom_parent.'",
								    "email": "'.$mail_parent.'",
								    "dayOfCycle": "'.$dayOfCycle.'",
								    "campaign": {
								        "campaignId": "'.$campaignId.'"
								    },
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
								        }
								    ]
								}';

								$params = json_decode($params);
								print_r($params);
								echo("<br>");
								// echo($mail_parent.' '.$ref_compte.' '.$statut);
								echo("<br>");

								$res = $getresponse->addContact($params);

								if($res -> httpStatus == "400"){
									$nb400Code++;
								}

								print_r($res);
								echo("<br><br> fin contact <br><br>");
							}else if($compte_associe == 2){
								if(!in_array($mail_parent, $mailParentInserted)){

									echo("debut contact avec 2 comptes<br><br>");
									// echo($mail_parent);
									// print_r($parent);
									// echo($dayOfCycle."<br><br>");
									if($statut == 'essai'){
										$dayOfCycle = intval($dayOfCycle);
										$campaignId = '4TPZW';
									}else{
										$dayOfCycle = intval($dayOfCycle)-10;
										$campaignId = '45XJl';

									}
									$prenom_proche_id = '3ytt8';
									$coupon_code_id = '3a93e';
									$prenom_proche_id_2 = '3zwiQ';
									$coupon_code_id_2 = '3zwLh';

									// on va récupérer le deuxième compte
									$req_second_account = $bdd->prepare("select parent.prenom as prenom_parent, coupon_code, statut, compte_eleve.ref_compte as refe_compte, compte_associe, eleve.ref_eleve as refe_eleve,
										lower(parent.adresse_mail) as mail_parent, eleve.prenom as prenom_eleve, EXTRACT(day FROM current_timestamp - date_creation  ) as dayofcycle
											from compte_eleve,eleve,parent 
											where compte_eleve.ref_parent = parent.ref_parent 
												and compte_eleve.ref_eleve = eleve.ref_eleve
												and statut in ('essai','inscrit')
												and compte_eleve.ref_parent = ?  and eleve.ref_eleve != ?");
									$req_second_account->execute(array($ref_parent,$ref_eleve));
									$second_account = $req_second_account->fetch();

									$prenom_eleve_2 = $second_account['prenom_eleve'];
									$coupon_code_2 = $second_account['coupon_code'];

									$params = '{
									    "name": "'.$prenom_parent.'",
									    "email": "'.$mail_parent.'",
									    "dayOfCycle": "'.$dayOfCycle.'",
									    "campaign": {
									        "campaignId": "'.$campaignId.'"
									    },
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
									        }
									    ]
									}';

									$params = json_decode($params);
									// print_r($params);
									echo("<br>");
									// echo($mail_parent.' '.$ref_compte.' '.$statut);
									echo("<br>");

									$res = $getresponse->addContact($params);

									if($res -> httpStatus == "400"){
										$nb400Code++;
									}

									print_r($res);
									echo("<br><br> fin contact <br><br>");

									$mailParentInserted[$nb_mail] = $mail_parent;
									$nb_mail++;
								}

							}


					

						}

					}


					$now = new DateTime(null,new \DateTimeZone("Europe/Paris"));
					to_log_slack(array("str1" => "cron exécuté le " .$now -> format('l jS \of F Y h:i:s A') ,"str2" => "insertion des comptes gr parents manquants ", "str3" => "nb code 400 : ".$nb400Code ));
					echo("<br><br> Done <br><br>");


?>