<?php 
		

				/*
					Ce script sert à insérer les adresses mails des élèves en essai et inscrit qui sont pas dans la bdd mais pas dans getresponse
					Il tourne avec un cron 10 fois pendant la nuit de 3h à 5h
					L'idée est de le faire tourner régulièrement pour remedier au probleme de timeout
					Il ne gère que les comptes de maths - physique
					hypo : la base de données est clean (on lui fait confiance)

				*/


					require_once('/home/clients/yopyopla/prod/spamtonprof/init_perso.php' );
					require_once(get_home_path().'/getresponse/GetResponseAPI3.class.php' );
					require_once(get_home_path().'/getresponse/functions_gr.php' );
					
					$nb400Code;

                    try
                    {
                        $bdd = new PDO('pgsql:host='.DB_HOST_PG.';port=5432;dbname='.DB_NAME_PG.';user='.DB_USER_PG.';password='.DB_PASSWORD_PG);

                    }
                    catch(Exception $e)
                    {
                            die('Erreur : '.$e->getMessage());
                    }
                    $getresponse = new GetResponse(GR_API);


					// pour récupérer tous les parents sans prenom de proche
					$eleveActif = $getresponse->getContactsSearchContacts('qh8Y');
					$eleveActif=json_decode(json_encode($eleveActif),true);
					$mailEleveInGR = [];
					$nb_mail = 0;


					foreach ($eleveActif as $contact){
						$mailEleveInGR[$nb_mail] =  strtolower($contact['email']);
						$nb_mail++;
					}




					$req_eleve = $bdd->prepare("select parent.prenom as prenom_parent, coupon_code, statut, compte_eleve.ref_compte as refe_compte,
						lower(eleve.adresse_mail) as mail_eleve, eleve.prenom as prenom, EXTRACT(day FROM current_timestamp - date_creation  ) as dayofcycle
							from compte_eleve,eleve,parent 
							where compte_eleve.ref_eleve = eleve.ref_eleve 
								and compte_eleve.ref_parent = parent.ref_parent
								and statut in ('essai','inscrit')
								and same_email = false
								and (maths = true or physique = true)");
					$req_eleve->execute();

					while($eleve = $req_eleve->fetch()){

						$prenom_eleve = $eleve['prenom'];
						$mail_eleve = $eleve['mail_eleve'];
						$prenom_parent = $eleve['prenom_parent'];
						$coupon_code = $eleve['coupon_code'];
						$statut = $eleve['statut'];
						$ref_compte = $eleve['refe_compte'];
						$dayOfCycle =$eleve['dayofcycle'];
						// echo($eleve['dayOfCycle']);
						if(!in_array($mail_eleve, $mailEleveInGR)){ //si pas dans getresponse
							echo("debut contact<br><br>");
							// print_r($eleve);
							// echo($dayOfCycle."<br><br>");
							if($statut == 'essai'){
								$dayOfCycle = intval($dayOfCycle);
								$campaignId = '4TP5I';
							}else{
								$dayOfCycle = intval($dayOfCycle)-10;
								$campaignId = '45X2f';

							}
							$prenom_proche_id = '3ytt8';
							$coupon_code_id = '3a93e';


							$params = '{
							    "name": "'.$prenom_eleve.'",
							    "email": "'.$mail_eleve.'",
							    "dayOfCycle": "'.$dayOfCycle.'",
							    "campaign": {
							        "campaignId": "'.$campaignId.'"
							    },
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
							        }
							    ]
							}';

							$params = json_decode($params);
							print_r($params);
							echo("<br>");
							// echo($mail_eleve.' '.$ref_compte.' '.$statut);
							echo("<br>");

							$res = $getresponse->addContact($params);


							if($res -> httpStatus == "400"){
								$nb400Code++;
							}

							print_r($res);
							echo("<br><br> fin contact <br><br>");
					

						}

					}

					$now = new DateTime(null,new \DateTimeZone("Europe/Paris"));

					to_log_slack(array("str1" => "cron exécuté le " .$now -> format('l jS \of F Y h:i:s A') ,"str2" => "insertion des comptes gr élèves manquants ", "str3" => "nb code 400 : ".$nb400Code ));


					echo("<br><br> Done <br><br>");


?>