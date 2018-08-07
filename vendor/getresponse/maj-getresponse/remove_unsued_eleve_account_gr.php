<?php 
		

				/*
					Ce script sert à retirer de getresponse les adresses mails des élèves en essai et inscrit qui sont plus en essai ou inscrit dans la bdd
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


					$req_eleve = $bdd->prepare("select lower(eleve.adresse_mail) as mail_eleve
							from compte_eleve,eleve,parent 
							where compte_eleve.ref_eleve = eleve.ref_eleve 
								and compte_eleve.ref_parent = parent.ref_parent
								and statut in ('essai','inscrit')
								and same_email = false");
					$req_eleve->execute();

					$mailEleveInBdd = [];
					$nb_mail_in_Bdd = 0;

					while($eleve = $req_eleve->fetch()){
						$mailEleveInBdd[$nb_mail_in_Bdd] =  strtolower($eleve['mail_eleve']);
						$nb_mail_in_Bdd++;

					}
					echo("nb mails eleve in base : ".$nb_mail_in_Bdd."<br>");
					// pour récupérer tous les élèves en essai ou inscrit dans getresponse
					$eleveActif = $getresponse->getContactsSearchContacts('qh8Y');
					$eleveActif=json_decode(json_encode($eleveActif),true);
					echo("nb mails eleve in GetResponse : ".count($eleveActif)."<br>");
					foreach ($eleveActif as $contact){
						if(!in_array(strtolower($contact['email']), $mailEleveInBdd)){ //si contact getresponse pas dans bdd
							print_r($contact['email']);

							$params = '{
							    "campaign": {
							    	"campaignId": "47CmU "
							    }
							}';
							$params = json_decode($params);
							print_r($getresponse->updateContact($contact['contactId'],$params));

							if($res -> httpStatus == "400"){
								$nb400Code++;
							}

						}
					}		


					$now = new DateTime(null,new \DateTimeZone("Europe/Paris"));

					to_log_slack(array("str1" => "cron exécuté le " .$now -> format('l jS \of F Y h:i:s A') ,"str2" => "suppresion des comptes gr élèves en trop ", "str3" => "nb code 400 : ".$nb400Code ));


					echo("<br><br> Done <br><br>");


?>