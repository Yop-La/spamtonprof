<?php 
                require_once('/home/clients/yopyopla/prod/spamtonprof/init_perso.php' );
                require_once(get_home_path().'/getresponse/GetResponseAPI3.class.php' );
                require_once(get_home_path().'/getresponse/functions_gr.php' );


                if(htmlspecialchars($_GET["password"]) == CRON_KEY){



                

                    try
                    {
                        $bdd = new PDO('pgsql:host='.DB_HOST_PG.';port=5432;dbname='.DB_NAME_PG.';user='.DB_USER_PG.';password='.DB_PASSWORD_PG);

                    }
                    catch(Exception $e)
                    {
                            die('Erreur : '.$e->getMessage());
                    }
                    $getresponse = new GetResponse(GR_API);

                    $jour_annee = date('z', time()) + 1;
                    $year = date('Y', time());

                    

                    

                    // champs de newsletters commun 

                    $campaignId = '4FpK2';  // campagne relance pour tout le monde
                    $fromFieldId = 'TQSmd'; // expe :seb pour tout le monde
                    $date_envoi = new DateTime();
                    $date_envoi->add(new DateInterval('P0Y0M0DT2H0M0S')); //on envoie 2 heures après l'exécution de se cron qui chaque jour à 6h30
                    $date_envoi = $date_envoi->format('c');

                    // -------------------------- partie relance pour les élèves --------------------------

                    // le mail de relance pour les élèves qui sont absents depuis 7 jours
                    $name = "Relance élève j7 (jour : ".$jour_annee."-".$year.")";
                    $subject = "7 jours sans aucune nouvelle :'(";
                    $HtmlContent  = '<table width="100%" cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" style="background-color: rgb(255, 255, 255);">
                        <tbody><tr>
                            <td align="center" valign="top" style="padding:0;margin:0;"><div class="WRAPPER" style="max-width: 600px; margin: auto;"><table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" width="600" class="wrapper" style="width: 600px;">
                                    <tbody>
                                    <tr>
                                        <td align="left" valign="top" style="margin:0;padding:0;">

                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block">
                                                <tbody><tr>
                                                    <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"><div>Bonjour [[name mode="ucfw"]],</div><div><br></div><div>Cela fait 7 jours depuis ton dernier mail !&nbsp;</div><div><br></div><div>7 jours sans aucune nouvelle, ça commence à être un peu long.</div><div><br></div><div>Que dirais tu de me contacter ? Tu auras plus de chance de progresser en me contactant au moins une fois par semaine.</div><div><br></div><div>Surtout, que tu peux me demander toute l\'aide que tu veux.</div><div><br></div><div>Pourquoi ne pas commencer par me dire ce que tu fais en ce moment à l\'école ?</div><div><br>Je t\'aiderai avec plaisir.</div><div><br></div><div>À bientôt,<br><br>Sébastien</div><div><br></div><div><span style="font-weight: bold;">p.s</span> : ce mail est une erreur ? Tu m\'as contacté récemment ? C\'est possible, dans ce cas, viens en discuter avec moi :)</div><div><br></div><div><span style="font-weight: bold;">p.p.s : </span>je préviendrai tes parents si on a toujours aucune nouvelle de ta part dans 3 jours. Viens en discuter avec moi parce que ce serait mieux d\'éviter ça.<br><br></div></td>
                                                </tr>
                                            </tbody></table>                                  
                                        </td>
                                    </tr>                               
                                </tbody></table></div>

                                
                            </td>
                        </tr>
                    </tbody></table>';


                    
                    $selectedSegments = ["qs5W"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);





                    // le mail de relance pour les élèves qui sont absents depuis 10 jours
                    $name = "Relance élève 10 (jour : ".$jour_annee."-".$year.")";
                    $subject = "10 jours sans aucune nouvelle :'(";
                    $HtmlContent =  '<table width="100%" cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" style="background-color: rgb(255, 255, 255);">
                    <tbody><tr>
                        <td align="center" valign="top" style="padding:0;margin:0;"><div class="WRAPPER" style="max-width: 600px; margin: auto;"><table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" width="600" class="wrapper" style="width: 600px;">
                                <tbody>
                                <tr>
                                    <td align="left" valign="top" style="margin:0;padding:0;">
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block">
                                            <tbody><tr>
                                                <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"><div>Bonjour [[name mode="ucfw"]],</div><div><br></div><div>Cela fait 10 jours depuis ton dernier mail !&nbsp;</div><div><br></div><div>10 jours sans aucune nouvelle, ça commence à être un peu long.</div><div><br></div><div>Que dirais tu de me contacter ? Tu auras plus de chance de progresser en me contactant au moins une fois par semaine.</div><div><br></div><div>Surtout, que tu peux me demander toute l\'aide que tu veux.</div><div><br></div><div>Pourquoi ne pas commencer par me dire ce sur quoi tu travailles en ce moment ?</div><div><br>Je t\'aiderai avec plaisir.</div><div><br></div><div>À bientôt,<br><br>Sébastien</div><div><br></div><div><span style="font-weight: bold;">p.s</span> : ce mail est une erreur ? Tu m\'as contacté récemment ? C\'est possible, dans ce cas, viens en discuter avec moi :)</div><div><br></div></td>
                                            </tr>
                                        </tbody></table>                                  
                                    </td>
                                </tr>                               
                            </tbody></table></div>
                            
                        </td>
                    </tr>
                </tbody></table>';

                    
                    $selectedSegments = ["qsjS"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);






                    // -------------------------- partie relance pour les parents avec un compte eleve au minimum  (inclue le premier compte eleve des parents avec deux compte eleve) --------------------------

                    // le mail de relance pour les parents avec un élève  absents depuis 10 jours (premier compte)
                    $name = "Relance parent j10 - 1er compte (jour : ".$jour_annee."-".$year.")";
                    $subject = "10 jours sans aucune nouvelle :'(";
                    $HtmlContent  = '<table width="100%" cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" style="background-color: rgb(255, 255, 255);">
                        <tbody><tr>
                            <td align="center" valign="top" style="padding:0;margin:0;"><div class="WRAPPER" style="max-width: 600px; margin: auto;"><table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" width="600" class="wrapper" style="width: 600px;">
                                    <tbody>
                                    <tr>
                                        <td align="left" valign="top" style="margin:0;padding:0;">

                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block">
                                                <tbody><tr>
                                                    <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"><div>Bonjour [[name mode="ucfw"]],<br><br>Il y a maintenant 10 jours que [[prenom_proche mode="ucfw"]] nous a contacté pour la dernière fois.<br><br>Que fait&nbsp;[[prenom_proche mode="ucfw"]] en ce moment ?&nbsp;</div><div><br></div><div>[[prenom_proche mode="ucfw"]] aura plus de chances d\'avoir de bons résultats en échangeant régulièrement avec moi.<br></div><div><br>D\'autant plus que [[prenom_proche mode="ucfw"]] peut me demander de l\'aide à tous moments. Pourriez vous demander à [[prenom_proche mode="ucfw"]] de m\'envoyer un mail pour me faire part de son travail ?<br><br>Je suis prêt à l\'aider !<br><br>À bientôt,<br><br>Sébastien</div><div><br>p.s : ce mail est une erreur ? [[prenom_proche mode="ucfw"]] m\'a contacté récemment ? C\'est possible, dans ce cas, venez en discuter avec moi :)<br></div></td>
                                                </tr>
                                            </tbody></table>                                  
                                        </td>
                                    </tr>                               
                                </tbody></table></div>

                                
                            </td>
                        </tr>
                    </tbody></table>';


                    
                    $selectedSegments = ["qsif"];
                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);


                    // le mail de relance pour les parents avec un élève  absents depuis 10 jours (deuxième compte)
                    $name = "Relance parent j10 - 2eme compte  (jour : ".$jour_annee."-".$year.")";
                    $subject = "10 jours sans aucune nouvelle :'(";
                    $HtmlContent  = '<table width="100%" cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" style="background-color: rgb(255, 255, 255);">
                        <tbody><tr>
                            <td align="center" valign="top" style="padding:0;margin:0;"><div class="WRAPPER" style="max-width: 600px; margin: auto;"><table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" width="600" class="wrapper" style="width: 600px;">
                                    <tbody>
                                    <tr>
                                        <td align="left" valign="top" style="margin:0;padding:0;">

                                            <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block">
                                                <tbody><tr>
                                                    <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"><div>Bonjour [[name mode="ucfw"]],<br><br>Il y a maintenant 10 jours que [[prenom_proche_2 mode="ucfw"]] nous a contacté pour la dernière fois.<br><br>Que fait&nbsp;[[prenom_proche_2 mode="ucfw"]] en ce moment ?&nbsp;</div><div><br></div><div>[[prenom_proche_2 mode="ucfw"]] aura plus de chances d\'avoir de bons résultats en échangeant régulièrement avec moi.<br></div><div><br>D\'autant plus que [[prenom_proche_2 mode="ucfw"]] peut me demander de l\'aide à tous moments. Pourriez vous demander à [[prenom_proche_2 mode="ucfw"]] de m\'envoyer un mail pour me faire part de son travail ?<br><br>Je suis prêt à l\'aider !<br><br>À bientôt,<br><br>Sébastien</div><div><br>p.s : ce mail est une erreur ? [[prenom_proche_2 mode="ucfw"]] m\'a contacté récemment ? C\'est possible, dans ce cas, venez en discuter avec moi :)<br></div></td>
                                                </tr>
                                            </tbody></table>                                  
                                        </td>
                                    </tr>                               
                                </tbody></table></div>

                                
                            </td>
                        </tr>
                    </tbody></table>';


                    
                    $selectedSegments = ["qsfc"];
                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);


                    echo('done');
                }else{

                    echo('erreur d\'authentification');
                }


?>
