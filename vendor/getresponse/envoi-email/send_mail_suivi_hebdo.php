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


                    $week = date('W', time());
                    $year = date('Y', time());

                    



                    // calcul du nb max et de la moyenne de mails envoyés la semaine dernière
                    $req = $bdd->prepare("select sum(nb_message_last_week)/count(nb_message_last_week) as moyenne from compte_eleve where statut in ('essai','inscrit')");
                    $req->execute();
                    $mean_nb_message = $req->fetch();
                    $mean_nb_message = round($mean_nb_message['moyenne'],0);

                    $req = $bdd->prepare("select max(nb_message_last_week) as maximum from compte_eleve where statut in ('essai','inscrit')");
                    $req->execute(array());
                    $max_nb_message_ar = $req->fetch();
                    $max_nb_message = $max_nb_message_ar['maximum'];

                    // les signatures communes à tous les mails

                    // signature élève

                    $signature_eleve = '<div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Pour te donner une idée de la semaine dernière :</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">1.&nbsp; l\'élève le plus actif a envoyé '.$max_nb_message.' messages</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">2. Tous les élèves ont envoyé '.$mean_nb_message.' messages en moyenne&nbsp;<br /><br /><strong>Attention : </strong>les chiffres de cet email sont à interpréter avec du recul. Envoyer 2 à 3 mails par semaine peut être très bien à condition que chacun de ces mails contiennent plusieurs pièces jointes d\'exercices.<br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">À bientôt,</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Sébastien</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family:Arial,Helvetica, sans-serif;">p.s : le nombre de messages envoyés est incorrect ? C\'est possible, dans ce cas, viens en discuter avec moi :)</span></div></td></tr></tbody></table></td></tr></tbody></table></div></td></tr></tbody></table>';

                    // signature parent
                    $signature_parent = '<div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Pour vous donner une idée de la semaine dernière :</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">1.&nbsp; l\'élève le plus actif a envoyé '.$max_nb_message.' messages</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">2. Tous les élèves ont envoyé '.$mean_nb_message.' messages en moyenne&nbsp;<br /><br /><strong>Attention : </strong>les chiffres de cet email sont à interpréter avec du recul. Envoyer 2 à 3 mails par semaine peut être très bien à condition que chacun de ces mails contiennent plusieurs pièces jointes d\'exercices.<br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">À bientôt,</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Sébastien</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family:Arial,Helvetica, sans-serif;">p.s : le nombre de messages envoyés est incorrect ? C\'est possible, dans ce cas, venez en discuter avec moi :)<br /><br />p.p.s : Vous voulez en savoir plus sur le suivi de [[prenom_proche mode="ucfw"]]? Il suffit de me le demander en répondant à ce mail.</span></div></td></tr></tbody></table></td></tr></tbody></table></div></td></tr></tbody></table>';

                    // signature pour les parents avec un deuxième compte
                    $signature_parent_2 = '<div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Pour vous donner une idée de la semaine dernière :</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">1.&nbsp; l\'élève le plus actif a envoyé '.$max_nb_message.' messages</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">2. Tous les élèves ont envoyé '.$mean_nb_message.' messages en moyenne&nbsp;<br /><br /><strong>Attention : </strong>les chiffres de cet email sont à interprétés avec du recul. Envoyer 2 à 3 mails par semaine peut être très bien à condition que chacun de ces mails contient plusieurs pièces jointes d\'exercices.<br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">À bientôt,</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Sébastien</span></div>
                    <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br /></span></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family:Arial,Helvetica, sans-serif;">p.s : le nombre de messages envoyés est incorrect ? C\'est possible, dans ce cas, venez en discuter avec moi :)<br /><br />p.p.s : Vous voulez en savoir plus sur le suivi de [[prenom_proche_2 mode="ucfw"]]? Il suffit de me le demander en répondant à ce mail.</span></div></td></tr></tbody></table></td></tr></tbody></table></div></td></tr></tbody></table>';





                    // champs de newsletters commun 

                    $campaignId = '4GWfY';  // campagne suivi hebdo pour tout le monde
                    $fromFieldId = 'TQSmd'; // expe :seb pour tout le monde
                    $date_envoi = new DateTime();
                    $date_envoi->add(new DateInterval('P0Y0M0DT2H0M0S'));
                    $date_envoi = $date_envoi->format('c');

                    // -------------------------- partie mail hebdo pour les élèves clients --------------------------

                    // le mail hebdo pour les élèves qui ont envoyés 0 messages la semaine dernière
                    $name = "Suivi hebdo élève - 0 message - (week".$week."-".$year.")";
                    $subject = "Aucun message de ta part la semaine dernière :'(";
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Bonjour [[name]], c\'est Sébastien, <br><br>Je viens de remarquer que tu ne m\'as envoyé aucun message la semaine dernière.<br><br>Que s\'est t\'il  passé ? Tu sais que tu peux me demander toute l\'aide que tu veux ?<br><br>Alors, n\'hésite plus. Je suis là pour t\'aider et je ferai mon maximum pour t\'aider à progresser.<br><br>Que dirais tu de commencer par me dire ce que tu fais en ce moment à l\'école ?<br></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><br></div>'.$signature_eleve;

                    
                    $selectedSegments = ["qdef"];
                    // $selectedCampaigns = [];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);





                    // le mail hebdo pour les élèves qui ont envoyés entre 0 et 5 messages la semaine dernière
                    $name = "Suivi hebdo élève - 0 et 5 messages - (week".$week."-".$year.")";
                    $subject = "Tu peux me demander beaucoup plus que ça !";
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Bonjour [[name]], c\'est Sébastien, <br><br>Je  viens de remarquer que tu m\'as envoyé [[nb_mails_last_week]] messages la semaine dernière.<br><br>C\'est un début mais tu peux en envoyer bien plus. <br><br>Il n\'y a pas de limite.<br><br>La régularité c\'est la clé pour progresser.<br><br>Alors, n\'hésite plus.<br></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><br></div>'.$signature_eleve;

                    
                    $selectedSegments = ["qdSc"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);





                    // le mail hebdo pour les élèves qui ont envoyés entre 5 et 10 messages la semaine dernière
                    $name = "Suivi hebdo élève - 5 et 10 messages - (week".$week."-".$year.")";
                    $subject = "Continue comme ça !";
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><div>Bonjour [[name]], c\'est Sébastien,&nbsp;</div><div><br>Je  viens de remarquer que tu m\'as envoyé [[nb_mails_last_week]] messages la semaine dernière.</div><div><br>Tu es sur la bonne voie.</div><div><br>C\'est comme ça que tu vas progresser.<br></div></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><br></div>'.$signature_eleve;

                    
                    $selectedSegments = ["qdNl"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);




                    // le mail hebdo pour les élèves qui ont envoyés plus de 10 messages la semaine dernière
                    $name = "Suivi hebdo élève - plus de 10 messages - (week".$week."-".$year.")";
                    $subject = "Bravo, plus qu'à rester régulier :)";
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Bonjour [[name]], c\'est Sébastien,&nbsp;</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Tu m\'as envoyé [[nb_mails_last_week]] messages la semaine dernière.</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">C\'est super !</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Il ne te reste plus qu\'à faire de même la semaine prochaine.</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Tu deviendras peut être l\'élève du mois.<br><br></span></div>'.$signature_eleve;

                    
                    $selectedSegments = ["qd1z"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);




                    // -------------------------- partie mail hebdo pour les parents clients avec un compte eleve au minimum  (inclue le premier compte eleve des parents avec deux compte eleve) --------------------------




                    // le mail hebdo pour les parents d'élèves client qui ont envoyés 0 messages la semaine dernière
                    $name = "Suivi hebdo parent - aucun message - (week".$week."-".$year.")";
                    $subject = 'Aucun message de [[prenom_proche mode="ucfw"]] la semaine dernière :\'(';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Bonjour c\'est Sébastien, <br><br>Je  viens de remarquer que [[prenom_proche mode="ucfw"]] ne m\'a envoyé aucun message la semaine dernière<br><br>Que s\'est t\'il  passé ? <br><br>Il n\'y pas à hésiter.  <br><br>Je suis là pour l\'aider et je ferai mon maximum pour sa progression.<br><br>Pouvez vous lui demander de m\'envoyer un message ?   <br><br>L\'idéal serait que [[prenom_proche mode="ucfw"]] me fasse part de son travail pour que je puisse l\'aider correctement. :)<br><br></div>'.$signature_parent;

                    
                    $selectedSegments = ["qRou"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);





                    // le mail hebdo pour les parents d'élèves client qui ont envoyés entre 0 et 5 messages la semaine dernière
                    $name = "Suivi hebdo parent - entre 1 et 5 messages - (week".$week."-".$year.")";
                    $subject = '[[prenom_proche mode="ucfw"]] peut m\'en demander beaucoup plus !';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Bonjour c\'est Sébastien, <br><br>Je  viens de remarquer que [[prenom_proche mode="ucfw"]] m\'a envoyé [[nb_mails_last_week]] message(s) la semaine dernière.<br><br>C\'est un début mais [[prenom_proche mode="ucfw"]] peut m\'en envoyer bien plus. Il n\'y a aucune limite.<br><br>Il n\'y pas à hésiter.<br><br>Je suis là pour l\'aider.<br></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><br></div>'.$signature_parent;

                    
                    $selectedSegments = ["qRrp"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);








                    // le mail hebdo pour les parents d'élèves client qui ont envoyés entre 5 et 10 messages la semaine dernière




                    $name = "Suivi hebdo parent - entre 5 et 10 messages - (week".$week."-".$year.")";
                    $subject = '[[prenom_proche mode="ucfw"]] est sur la bonne voie';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><div><div>Bonjour c\'est Sébastien, <br><br>Je  viens de remarquer que [[prenom_proche mode="ucfw"]] m\'a envoyé [[nb_mails_last_week]] message la semaine dernière.<br><br>C\'est un début mais [[prenom_proche mode="ucfw"]] peut m\'en envoyer bien plus. Il n\'y a aucune limite.<br><br>Il n\'y pas à hésiter.<br><br>Je suis là pour l\'aider.</div></div></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><br></div>'.$signature_parent;

                    
                    $selectedSegments = ["qRMQ"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);







                    // le mail hebdo pour les parents d'élèves client qui ont envoyés plus de 10 messages la semaine dernière





                    $name = "Suivi hebdo parent - plus de 10 messages - (week".$week."-".$year.")";
                    $subject = '[[prenom_proche mode="ucfw"]] doit continuer comme ça !';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Bonjour c\'est Sébastien,&nbsp;</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">[[prenom_proche mode="ucfw"]] m\'a envoyé [[nb_mails_last_week]] messages la semaine dernière.</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">C\'est super !</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">J\'espère qu\'il fera au moins autant la semaine prochaine.</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Il deviendra peut être l\'élève du mois.</span></div><div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> '.$signature_parent;

                    
                    $selectedSegments = ["qRQt"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);







                    // -------------------------- partie mail hebdo pour les parents clients avec un deuxième compte  --------------------------


                    // le mail hebdo pour les parents d'élèves client qui ont envoyés 0 messages la semaine dernière



                    $name = "Suivi hebdo parent_2 - aucun message - (week".$week."-".$year.")";
                    $subject = 'Aucun message de [[prenom_proche_2 mode="ucfw"]] la semaine dernière :\'(';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Bonjour c\'est Sébastien, <br><br>Je  viens de remarquer que [[prenom_proche_2 mode="ucfw"]] ne m\'a envoyé aucun message la semaine dernière<br><br>Que s\'est t\'il  passé ? <br><br>Il n\'y pas à hésiter.  <br><br>Je suis là pour l\'aider et je ferai mon maximum pour sa progression.<br><br>Pouvez vous lui demander de m\'envoyer un message ?   <br><br>L\'idéal serait que [[prenom_proche_2 mode="ucfw"]] me fasse part de son travail pour que je puisse l\'aider correctement. :)<br><br></div>'.$signature_parent;

                    
                    $selectedSegments = ["qR5h"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);




                    // le mail hebdo pour les parents d'élèves client qui ont envoyés entre 0 et 5 messages la semaine dernière





                    $name = "Suivi hebdo parent_2 - entre 1 et 5 messages - (week".$week."-".$year.")";
                    $subject = '[[prenom_proche_2 mode="ucfw"]] peut m\'en demander beaucoup plus !';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Bonjour c\'est Sébastien, <br><br>Je  viens de remarquer que [[prenom_proche_2 mode="ucfw"]] m\'a envoyé [[nb_mails_last_week_2]] message(s) la semaine dernière.<br><br>C\'est un début mais [[prenom_proche_2 mode="ucfw"]] peut m\'en envoyer bien plus. Il n\'y a aucune limite.<br><br>Il n\'y pas à hésiter.<br><br>Je suis là pour l\'aider.<br></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><br></div>'.$signature_parent;

                    
                    $selectedSegments = ["qRZG"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);




                    // le mail hebdo pour les parents d'élèves client qui ont envoyés entre 5 et 10 messages la semaine dernière






                    $name = "Suivi hebdo parent_2 - entre 5 et 10 messages - (week".$week."-".$year.")";
                    $subject = '[[prenom_proche_2 mode="ucfw"]] est sur la bonne voie';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><div><div>Bonjour c\'est Sébastien, <br><br>Je  viens de remarquer que [[prenom_proche_2 mode="ucfw"]] m\'a envoyé [[nb_mails_last_week_2]] message la semaine dernière.<br><br>C\'est un début mais [[prenom_proche_2 mode="ucfw"]] peut m\'en envoyer bien plus. Il n\'y a aucune limite.<br><br>Il n\'y pas à hésiter.<br><br>Je suis là pour l\'aider.</div></div></div><div style="color: #262626; font-family: Arial, Helvetica, sans-serif; font-size: 16px;"><br></div>'.$signature_parent;

                    
                    $selectedSegments = ["qRL6"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);








                    // le mail hebdo pour les parents d'élèves client qui ont envoyés plus de 10 messages la semaine dernière






                    $name = "Suivi hebdo parent_2 - plus de 10 messages - (week".$week."-".$year.")";
                    $subject = '[[prenom_proche_2 mode="ucfw"]] doit continuer comme ça !';
                    $HtmlContent = '<table cellpadding="0" cellspacing="0" border="0" data-mobile="true" dir="ltr" align="center" data-width="600" width="100%" style="background-color: rgb(255, 255, 255);"> <tbody> <tr> <td align="center" valign="top" style="padding: 0; margin: 0;"> <div class="WRAPPER" style="max-width: 600px; margin: auto;"> <table align="center" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" class="wrapper" width="600" style="width: 600px;"> <tbody> <tr> <td align="left" valign="top" style="margin: 0; padding: 0;"> <table border="0" cellpadding="0" cellspacing="0" align="center" data-editable="text" class="text-block" style="width: 100%;"> <tbody> <tr> <td align="left" valign="top" class="lh-1" style="padding: 0px; margin: 0px; font-family: Arial, Helvetica, sans-serif; color: rgb(38, 38, 38); font-size: 16px; line-height: 1.15;"> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Bonjour c\'est Sébastien,&nbsp;</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">[[prenom_proche_2 mode="ucfw"]] m\'a envoyé [[nb_mails_last_week_2]] messages la semaine dernière.</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">C\'est super !</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">J\'espère qu\'il fera au moins autant la semaine prochaine.</span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> <div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;">Il deviendra peut être l\'élève du mois.</span></div><div><span color="#262626" face="Arial, Helvetica, sans-serif" style="color: #262626; font-family: Arial, Helvetica, sans-serif;"><br></span></div> '.$signature_parent;

                    
                    $selectedSegments = ["qRfD"];

                    $params = getParamsToPostNewsLetters($name, $subject,$campaignId, $date_envoi,$fromFieldId, $HtmlContent,array(),  $selectedSegments);
                    $res = $getresponse->sendNewsletter($params);
                    print_r($res);

                    echo('done');
                }else{

                    echo('erreur d\'authentification');
                }


?>
