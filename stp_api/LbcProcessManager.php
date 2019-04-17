<?php
namespace spamtonprof\stp_api;

use spamtonprof;

class LbcProcessManager
{

    public $slack, $gmailManager, $prospectLbcMg, $messProspectMg, $lbcAccountMg, $msgs, $errors, $gmailAccountMg, $gmailAccount, $renewalUrlMg;

    public function __construct()
    {
        $this->slack = new \spamtonprof\slack\Slack();
        
        $this->gmailManager = new \spamtonprof\googleMg\GoogleManager("mailsfromlbc@gmail.com");
        $this->prospectLbcMg = new \spamtonprof\stp_api\ProspectLbcManager();
        $this->messProspectMg = new \spamtonprof\stp_api\MessageProspectLbcManager();
        $this->lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $this->gmailAccountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
        $this->messageTypeMg = new \spamtonprof\stp_api\LeadMessageTypeManager();
        $this->renewalUrlMg = new \spamtonprof\stp_api\LbcRenewalUrlManager();
        
        $this->gmailAccount = $this->gmailAccountMg->get("mailsfromlbc@gmail.com");
        
        $this->msgs = [];
        $this->errors = [];
    }

    /*
     * cette function lit les nouveaux messages ( ie les messages dont l'history id est plus grand que le dernier history id ).
     *
     * Première fonction : elle lit les messages de lead pour :
     *
     * - les stocker dans message_prospect_lbc
     * - envoyer une notif à slack
     * - leur attribuer un libellé correspondant à leur type
     *
     * Il y a 3 types :
     * - type message-direct : message direct ( on a l'adresse du prospect ) -> signature : "https://www.leboncoin.fr/phishing.htm"
     * - type debut-messagerie-leboncoin : messagerie leboncoin ( premier message du lead ) -> signature : "Nouveau message concernant l'annonce"
     * - type conversation-messagerie-leboncoin : messagerie leboncoin ( conversation avec lead ) -> signature : "via leboncoin a "
     *
     * Deuxième fonction : elle lit les messages envoyés par le.bureau.des.profs@gmail.com pour :
     * - en extraire la réponse de l'agent de prospection et le stocker dans la colonne reply de la table message_prospect_lbc
     *
     */
    public function readNewLeadMessages()
    {
        $lastHistoryId = $this->gmailAccount->getLast_history_id();
        
        echo("Dernier history id ( avant process ) : " . $lastHistoryId . '<br>');
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $now->sub(new \DateInterval("PT2H"));
        
        $timestamp = $now->getTimestamp();
        
        $now = $now->format('Y/m/d');
        
        $retour = $this->gmailManager->getNewMessages($lastHistoryId);
        
        $messages = $retour["messages"];
        
        $lastHistoryId = $retour["lastHistoryId"];
        
        $this->gmailAccount->setLast_history_id($lastHistoryId);
        $this->gmailAccountMg->updateHistoryId($this->gmailAccount);
        
        echo ("------  nb messages : " . count($messages) . " ----- <br>");
        
        $nbMessageToProcess = 100;
        $indexMessageProcessed = 0;
        
        foreach ($messages as $message) {
            
            $gmailId = $message->id;
            $historyId = $message->historyId;
            
            $from = $this->gmailManager->getHeader($message, "From");
            $snippet = $message->snippet;
            $subject = $this->gmailManager->getHeader($message, "Subject");
            $to = $this->gmailManager->getHeader($message, "To");
            $messageId = $this->gmailManager->getHeader($message, "Message-Id");
            $body = $this->gmailManager->getBody($message);
            
            $timeStamp = $message->internalDate / 1000;
            $dateReception = new \DateTime();
            $dateReception->setTimestamp($timeStamp);
            $dateReception->setTimezone(new \DateTimeZone('Europe/Paris'));
            
            $messageType = 0;
            
            // if a faire en premier car le premier if est inclus dans le deuxieme if
            if (strpos($from, 'sender@mailer1.33mail.com') !== false) { // message du bon coin a priori via 33mail
                
                if (strpos($body, 'https://www.leboncoin.fr/phishing.htm') !== false) {
                    
                    $messageType = $this->messageTypeMg::MESSAGE_DIRECT;
                } elseif (strpos($body, 'via leboncoin a ') !== false) {
                    
                    $messageType = $this->messageTypeMg::CONVERSATION_MESSAGERIE_LEBONCOIN;
                } elseif (strpos($subject, "Nouveau message concernant l'annonce") !== false) {
                    
                    $messageType = $this->messageTypeMg::DEBUT_MESSAGERIE_LEBONCOIN;
                }
                
                if ($messageType != 0) {
                    
                    $emails = [];
                    
                    $pattern = "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i";
                    preg_match_all($pattern, $snippet, $emails);
                    
                    $lbcProfil = $emails[0][0];
                    $leadEmail = $emails[0][1];
                    
                    echo ($body . "<br>");
                    echo ("lbcProfil : " . $lbcProfil . " -- leadEmail : " . $leadEmail . " -- messageId : " . $messageId . " -- dateReception : " . $dateReception->format(PG_DATETIME_FORMAT) . "  --  type : " . $messageType . "<br>" . "<br>");
                    
                    // on ajoute a table lead messages
                    
                    $this->addNewLeadMessage($lbcProfil, $leadEmail, $dateReception, $gmailId, $subject, $messageType);
                    
                    // on attribue le libelle coresspondant a la categorie
                    
                    $type = $this->messageTypeMg->get($messageType);
                    
                    $labelId = $this->gmailManager->getLabelsIds(array(
                        $type->getType()
                    ));
                    
                    $this->gmailManager->modifyMessage($gmailId, $labelId, array());
                    
                    // on envoie un message dans slacks
                    
                    $this->msgs[] = "------------------------";
                    $this->msgs[] = "Nouveau message ! gmailId : " . $gmailId;
                    $this->msgs[] = "date de reception : " . $dateReception->format(PG_DATETIME_FORMAT);
                    $this->msgs[] = "   ------   ";
                    $this->msgs[] = strip_tags($body);
                    
                    if (count($this->msgs) != 0) {
                        // $this->slack->sendMessages($this->slack::LogLbc, $this->msgs);
                        $this->msgs = [];
                    }
                    
                    if (count($this->errors) != 0) {
                        
                        $this->errors = array_merge(array(
                            "--------- debut des erreurs ---------"
                        ), $this->errors);
                        $this->errors[] = "--------- fin des erreurs ---------";
                        $this->slack->sendMessages($this->slack::LogLbc, $this->errors);
                        $this->errors = [];
                    }
                }
                
                // if a faire en deuxieme car ce deuxieme inclus le premier
            } elseif (strpos($from, 'messagerie.leboncoin.fr') !== false) { // message du bon coin a priori via mailgun ou via gmx
                
                if (strpos($body, 'https://www.leboncoin.fr/phishing.htm') !== false) {
                    
                    $messageType = $this->messageTypeMg::MESSAGE_DIRECT;
                } elseif (strpos($body, 'via leboncoin a ') !== false) {
                    
                    $messageType = $this->messageTypeMg::CONVERSATION_MESSAGERIE_LEBONCOIN;
                } elseif (strpos($subject, "Nouveau message concernant l'annonce") !== false) {
                    
                    $messageType = $this->messageTypeMg::DEBUT_MESSAGERIE_LEBONCOIN;
                }
                
                // Extraction de l'adresse mail du champ to
                $matches = [];
                $pattern = "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i";
                preg_match_all($pattern, $to, $matches);
                $to = $matches[0][0];
                
                // Extraction de l'adresse mail du champ from
                $matches = [];
                $pattern = "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i";
                preg_match_all($pattern, $from, $matches);
                $from = $matches[0][0];
                
                if ($messageType != 0) {
                    
                    $emails = [];
                    
                    $pattern = "/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i";
                    preg_match_all($pattern, $snippet, $emails);
                    
                    $lbcProfil = $to;
                    $leadEmail = $from;
                    
                    echo ($body . "<br>");
                    echo ("lbcProfil : " . $lbcProfil . " -- leadEmail : " . $leadEmail . " -- messageId : " . $messageId . " -- dateReception : " . $dateReception->format(PG_DATETIME_FORMAT) . "  --  type : " . $messageType . "<br>" . "<br>");
                    
                    // on ajoute a table lead messages
                    
                    $this->addNewLeadMessage($lbcProfil, $leadEmail, $dateReception, $gmailId, $subject, $messageType);
                    
                    // on attribue le libelle coresspondant a la categorie
                    
                    $type = $this->messageTypeMg->get($messageType);
                    
                    $labelNames = array(
                        $type->getType()
                    );
                    
                    $mailBox = 'mailgun';
                    if (strpos($lbcProfil, 'gmx') !== false) {
                        $mailBox = 'gmx';
                    }
                    $labelNames[] = $mailBox;
                    
                    $labelId = $this->gmailManager->getLabelsIds($labelNames);
                    
                    $this->gmailManager->modifyMessage($gmailId, $labelId, array());
                    
                    if (count($this->errors) != 0) {
                        
                        $this->errors = array_merge(array(
                            "--------- debut des erreurs ---------"
                        ), $this->errors);
                        $this->errors[] = "--------- fin des erreurs ---------";
                        $this->slack->sendMessages($this->slack::LogLbc, $this->errors);
                        $this->errors = [];
                    }
                }
            } elseif (strpos($from, 'le.bureau.des.profs@gmail.com') !== false) {
                
                if (strpos($subject, "|--|") !== false) {
                    
                    preg_match('/\|--\|(\d*)\|--\|/', $subject, $matches);
                    
                    $refMessage = $matches[1];
                    
                    $stpMessage = $this->messProspectMg->get(array(
                        "ref_message" => $refMessage
                    ));
                    
                    if ($stpMessage) {
                        
                        $body = $this->gmailManager->getBody($message);
                        
                        $stpMessage->setReply($body);
                        
                        $this->messProspectMg->updateReply($stpMessage);
                        
                        $stpMessage->setAnswer_gmail_id($gmailId);
                        
                        $this->messProspectMg->updateAnswerGmailId($stpMessage);
                        
                        // attribuer un libelle pour dire que le message a ete lu
                        $labelId = $this->gmailManager->getLabelsIds(array(
                            "bot_read_it"
                        ));
                        
                        $this->gmailManager->modifyMessage($gmailId, $labelId, array());
                    }
                }
            } elseif (strpos(strtolower($subject), "renouvelez gratuitement") !== false) {
                
                try {
                    
                    $to = extractFirstMail($to);
                    
                    
                    $urls = extract_url($body);
                    $url = $urls[0][2];
                    
                    $url = htmlspecialchars_decode($url);
                    
                    
                    
                    $act = $this->lbcAccountMg->get(array(
                        "mail" => $to
                    ));
                    
                    $renewalUrl = $this->renewalUrlMg->add(new \spamtonprof\stp_api\LbcRenewalUrl(array(
                        "url" => $url,
                        "statut" => $this->renewalUrlMg::TO_RENEW,
                        "ref_compte_lbc" => $act->getRef_compte(),
                        "date_reception" => $dateReception->format(PG_DATETIME_FORMAT)
                    )));
                    
                    // attribuer un libelle pour dire que le message a ete lu
                    $labelId = $this->gmailManager->getLabelsIds(array(
                        "bot_read_it"
                    ));
                    
                    $this->gmailManager->modifyMessage($gmailId, $labelId, array());
                    
                    $this->slack->sendMessages('log-lbc', array(
                        "Récupération du lien de renouvellement d'annonce :" . $url,
                        "Avec pour compte : " . $act->getMail()
                    ));
                } catch (\Exception $e) {
                    
                    $this->slack->sendMessages('log-lbc', array(
                        "Erreur lors de la récupération du lien de renouvellement d'annonce :" . $url,
                        "Avec pour compte : " . $act->getMail()
                    ));
                }
            }
            
            $indexMessageProcessed ++;
            if ($nbMessageToProcess == $indexMessageProcessed) {
                break;
            }
        }
    }

    public function processNewMessages()
    {
        
        // pour traiter les messages de leads si il y en a et les transferer au bureau des profs
        for ($i = 0; $i < 5; $i ++) {
            
            $this->processLeadMessage();
        }
        
        // pour envoyer au prospect les messages envoyes par le service prospection
        
        for ($i = 0; $i < 1; $i ++) {
            
            $this->replyToLeadMessages();
        }
    }

    public function forwadLeadMessages(\spamtonprof\stp_api\MessageProspectLbc $message)
    {
        $gmailId = $message->getGmail_id();
        $subject = $message->getSubject();
        $refMessage = $message->getRef_message();
        
        $gMessage = $this->gmailManager->getMessage($gmailId, [
            "format" => "full"
        ]);
        
        $body = $this->gmailManager->getBody($gMessage);
        
        $subject = "|--|" . $refMessage . "|--| " . $subject;
        $replyTo = "mailsfromlbc@gmail.com";
        if ($message->getType() == $this->messageTypeMg::MESSAGE_DIRECT) {
            
            $lead = $this->prospectLbcMg->get(array(
                "ref_prospect_lbc" => $message->getRef_prospect_lbc()
            ));
            
            $replyTo = $lead->getAdresse_mail();
            $subject = $message->getSubject();
        }
        
        $this->gmailManager->sendMessage($body, $subject, "le.bureau.des.profs@gmail.com", $replyTo, "mailsfromlbc@gmail.com", "lbcBot");
        
        $message->setProcessed(true);
        $this->messProspectMg->updateProcessed($message);
    }

    private function addNewLeadMessage($emailAccountLbc, $contactLbc, $dateReception, $gmailId, $subject, $messageType)
    {
        
        // determination du compte leboncoin associe au messsage
        $compteLbc = $this->lbcAccountMg->get(array(
            'mail' => $emailAccountLbc
        ));
        
        if (! $compteLbc) {
            $this->errors[] = "Impossible de trouver ce compte lbc dans la base : " . $emailAccountLbc;
            $this->errors[] = " ---- ";
            return;
        }
        
        // enregistrement du prospect si il n'existe pas
        $prospectLbc = $this->prospectLbcMg->get(array(
            "adresse_mail" => $contactLbc
        ));
        if (! $prospectLbc) {
            
            $prospectLbc = new \spamtonprof\stp_api\ProspectLbc();
            $prospectLbc->setAdresse_mail($contactLbc);
            $prospectLbc = $this->prospectLbcMg->add($prospectLbc);
        }
        
        // enregistrement du messge du prospect
        $mess = new \spamtonprof\stp_api\MessageProspectLbc();
        
        $mess->setDate_reception($dateReception);
        $mess->setRef_compte_lbc($compteLbc->getRef_compte());
        $mess->setProcessed(false);
        $mess->setRef_prospect_lbc($prospectLbc->getRef_prospect_lbc());
        $mess->setGmail_id($gmailId);
        $mess->setSubject($subject);
        $mess->setType($messageType);
        $mess->setAnswered(false);
        
        // return;
        
        $mess = $this->messProspectMg->add($mess);
    }

    public function replyToLeadMessages()
    {
        $message = $this->messProspectMg->getMessageToSend();
        
        if ($message) {
            
            $compteLbc = $this->lbcAccountMg->get(array(
                "ref_compte" => $message->getRef_compte_lbc()
            ));
            
            $lead = $this->prospectLbcMg->get(array(
                "ref_prospect_lbc" => $message->getRef_prospect_lbc()
            ));
            
            $subject = 'Re: ' . str_replace('leboncoin', 'lebonc...', $message->getSubject());
            $body = $message->getReply();
            
            $to = $lead->getAdresse_mail(); // 'alex.guillemine@gmail.com'
                                            
            // on supprime la partie ecrite par 33mail.
            $pattern = '/(<div align="center">.*?<\/div><\/div>)|(This email was sent to the alias(.*?)[\r\n])/';
            $body = preg_replace_callback($pattern, function ($m) {
                return ("");
            }, $body);
            
            $send = $this->sendLeadReply($compteLbc, $subject, $to, $body, $message);
            
            if ($send) {
                
                $message->setAnswered(true);
                $this->messProspectMg->updateAnswered($message);
                
                $msgs = array();
                $msgs[] = " ------------------------ ";
                $msgs[] = "Reponse automatique au mail : " . $message->getRef_message();
                $msgs[] = "Lead concerne : " . $lead->getAdresse_mail();
                $msgs[] = "Compte Lbc concerne et expediteur : " . $compteLbc->getMail();
                $msgs[] = "Reponse : ";
                $msgs[] = $subject;
                $msgs[] = strip_tags($body);
                // $this->slack->sendMessages($this->slack::LogLbc, $msgs);
                
                $labelId = $this->gmailManager->getLabelsIds(array(
                    "Repondu"
                ));
                
                $this->gmailManager->modifyMessage($message->getGmail_id(), $labelId, array());
                $this->gmailManager->modifyMessage($message->getAnswer_gmail_id(), $labelId, array());
            } else {
                $slack = new \spamtonprof\slack\Slack();
                $slack->sendMessages("log-lbc", array(
                    "La reponse au lead de ref " . $message->getRef_message() . " n'a pas pu etre envoye ... "
                ));
            }
        }
    }

    public function sendLeadReply(\spamtonprof\stp_api\LbcAccount $compteLbc, $subject, $to, $body, $message)
    {
        $clientMg = new \spamtonprof\stp_api\LbcClientManager();
        $client = $clientMg->get(array(
            "ref_client" => $compteLbc->getRef_client()
        ));
        
        if (strpos($compteLbc->getMail(), 'gmx') !== false) {
            
            $gmxActMg = new \spamtonprof\stp_api\GmxActManager();
            $gmxAct = $gmxActMg->get(array(
                'ref_compte_lbc' => $compteLbc->getRef_compte()
            ));
            
            $smtpServer = new \spamtonprof\stp_api\SmtpServer(array(
                'host' => 'mail.gmx.com',
                'port' => 587,
                'password' => $gmxAct->getPassword(),
                'username' => $gmxAct->getMail()
            ));
        } else {
            $smtpServerMg = new \spamtonprof\stp_api\SmtpServerManager();
            $smtpServer = $smtpServerMg->get(array(
                "ref_smtp_server" => $smtpServerMg::smtp2Go
            ));
        }
        
        $send1 = $smtpServer->sendEmail($subject, $to, $body, $compteLbc->getMail(), $compteLbc->getPrenom(), false);
        $send2 = $smtpServer->sendEmail("Stp Reply : |--|" . $message->getRef_message() . "|--|" . $subject, "lebureaudesprofs@gmail.com", $body, $compteLbc->getMail(), $compteLbc->getPrenom(), false);
        
        return ($send1 && $send2);
    }

    public function processLeadMessage()
    {
        $message = $this->messProspectMg->getLastLeadMessage();
        
        if ($message) {
            
            $slack = new \spamtonprof\slack\Slack();
            $slack->sendMessages('message-lbc', array(
                "Nouveau message du bon coin"
            ));
            
            $this->forwadLeadMessages($message);
            
            // on attribue le libelle pour dire que le message a ete transfere
            
            $labelId = $this->gmailManager->getLabelsIds(array(
                "forwarded"
            ));
            
            $this->gmailManager->modifyMessage($message->getGmail_id(), $labelId, array());
        }
    }

    // cette fonction permet de controler les annonces en ligne des nbCompte derniers comptes actifs (ie qui n'a pas desactive par leboncoin)
    // --- step 1 : recuperation des nb derniers comptes actifs ( on pourrait specifier un autre critere de recuperation des comptes )
    // --- step 2 : on supprime toutes les annonces dans la table adds_tempo comme le compte va de nouveau etre controle
    // --- step 3 : on recupere les potentiels annonces en ligne de ces comptes avec l'api du bon coin
    
    // --- step 4-1 (si il y a des annonces en ligne)
    // --- step 4-1-1 : on les ajoute a la table adds_tempo
    // --- step 4-1-2 : on met a jour la ref_commune des annonces ajoutes a adds_tempo
    // --- step 5 : on desactive ou on active le compte
    // --- step 6 : on met a jour le nb d'annonce du compte lbc
    // --- step 7 : on met a jour la de controle
    public function checkAds($nbCompte)
    {
        $lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $lbcApi = new \spamtonprof\stp_api\LbcApi();
        $adTempoMg = new \spamtonprof\stp_api\AddsTempoManager();
        $slack = new \spamtonprof\slack\Slack();
        
        // step 1 :recuperer les comptes ages d'au moins 2h.
        $lbcAccounts = $lbcAccountMg->getAccountToScrap($nbCompte);
        
        foreach ($lbcAccounts as $lbcAccount) {
            
            $msgs = [];
            $msgs[] = "Controle de " . $lbcAccount->getRef_compte();
            
            $codePromo = $lbcAccount->getCode_promo();
            $user_id = $lbcAccount->getUser_id();
            
            // step 2 : suppression des annonces dans la base
            $adTempoMg->deleteAll(array(
                "ref_compte" => $lbcAccount->getRef_compte()
            ));
            
            // step 3 : recuperation des annonces via api leboncoin
            
            $ads = false;
            
            if ($user_id) {
                $ads = $lbcApi->getAdds(array(
                    'user_id' => $user_id
                ));
                $msgs[] = "User_id : " . $user_id;
            } else if (! is_null($codePromo)) {
                $ads = $lbcApi->getAdds(array(
                    "code_promo" => $codePromo
                ));
                $msgs[] = "Pas de user_id : ";
            }
            
            // step 4-1 : si il y a des annonces en ligne sur leboncoin
            $disabled = false;
            $nbAnnonce = 0;
            if ($ads) {
                
                $ads = $ads->ads;
                
                foreach ($ads as $ad) {
                    
                    $firstPublicationDate = $ad->first_publication_date;
                    $zipcode = $ad->location->zipcode;
                    $city = $ad->location->city;
                    $id = $ad->list_id;
                    $hasPhone = $ad->has_phone;
                    
                    // 4-1-1 : on ajoute ces annonces a adds_tempo
                    $adTempo = new \spamtonprof\stp_api\AddsTempo(array(
                        "first_publication_date" => $firstPublicationDate,
                        "zipcode" => $zipcode,
                        "city" => $city,
                        "id" => $id,
                        "has_phone" => $hasPhone,
                        "ref_compte" => $lbcAccount->getRef_compte()
                    ));
                    $adTempoMg->add($adTempo);
                    $nbAnnonce ++;
                }
                
                // 4-1-2 : on va mettre a jour la ref_commune de adds_tempo
                $adsTemp = $adTempoMg->getAll(array(
                    "ref_compte" => $lbcAccount->getRef_compte()
                ));
                
                $adTempoMg->updateAllRefCommune($adsTemp);
            } else {
                $disabled = true;
                $nbAnnonce = 0;
            }
            // --- step 5 : on desactive ou on active le compte
            $lbcAccount->setDisabled($disabled);
            $lbcAccountMg->updateDisabled($lbcAccount);
            
            // --- step 6 : on met a jour le nb d'annonce du compte lbc
            $lbcAccount->setNb_annonces_online($nbAnnonce);
            $lbcAccountMg->updateNbAnnonceOnline($lbcAccount);
            
            // --- step 7 : on met a jour la date de controle
            $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
            $lbcAccount->setControle_date($now);
            $lbcAccountMg->updateControleDate($lbcAccount);
            
            $msgs[] = $nbAnnonce . "en ligne";
            $slack->sendMessages("log-lbc", $msgs);
        }
    }

    // pour generer et retourner les annonces avant publication par zenno
    public function generateAds($refClient, $nbAds, $phone, $lock = false, $ref_compte = false)
    {
        
        // on recupere le client
        $clientMg = new \spamtonprof\stp_api\LbcClientManager();
        $client = $clientMg->get(array(
            'ref_client' => $refClient
        ));
        
        // on recupere les titres
        $hasTypeTitleMg = new \spamtonprof\stp_api\HasTitleTypeManager();
        $lbcTitleMg = new \spamtonprof\stp_api\LbcTitleManager();
        $communeMg = new \spamtonprof\stp_api\LbcCommuneManager();
        $adMg = new \spamtonprof\stp_api\AddsTempoManager();
        $actMg = new \spamtonprof\stp_api\LbcAccountManager();
        
        $hasTypeTitle = $hasTypeTitleMg->get(array(
            "ref_client_defaut" => $refClient
        ));
        $titles = $lbcTitleMg->getAll(array(
            "ref_type_titre" => $hasTypeTitle->getRef_type_titre()
        ));
        shuffle($titles);
        
        // on recupere les textes
        $hasTypeTexteMg = new \spamtonprof\stp_api\HasTextTypeManager();
        $hasTypeTexte = $hasTypeTexteMg->get_next($refClient);
        
        $lbcTexteMg = new \spamtonprof\stp_api\LbcTexteManager();
        $textes = $lbcTexteMg->getAll(array(
            "ref_type_texte.valid" => $hasTypeTexte->getRef_type()
        ));
        shuffle($textes);
        
        // on recupere le compte lbc pour avoir le prenom a mettre dans les annonces
        $prenom = '[prenom]';
        if ($ref_compte) {
            $act = $actMg->get(array(
                'ref_compte' => $ref_compte
            ));
            $prenom = $act->getPrenom();
            
            $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
            
            $act->setDate_publication($now);
            $actMg->update_date_publication($act);
        }
        
        // on ajoute le num tel aux textes si demande
        if ($phone != 'pas-de-num') {
            $textes = $lbcTexteMg->addPhoneLine($textes, $phone);
        }
        
        // on recupere les communes
        $communes = $communeMg->getAll(array(
            "ref_client" => $refClient
        ));
        
        // on constitue les annonces ( en verouillant les communes de ces annonces)
        $nbTitles = count($titles);
        $nbTextes = count($textes);
        $nbCommunes = count($communes);
        
        // recuperation des images
        $images = scandir(ABSPATH . 'wp-content/uploads/lbc_images/' . $client->getImg_folder());
        
        unset($images[0]);
        unset($images[1]);
        
        shuffle($images);
        
        $nbImages = count($images);
        
        $ads = [];
        for ($i = 0; $i < $nbAds; $i ++) {
            
            // recuperation du titre
            $title = $titles[$i % $nbTitles];
            $title = $title->getTitre();
            
            // recuperation du texte
            $texte = $textes[$i % $nbTextes];
            
            serializeTemp($texte);
            
            $texte->setTexte(str_replace(array(
                'Alexandre',
                'alexandre',
                'Anahyse',
                'anahyse'
            ), $prenom, $texte->getTexte()));
            
            // recuperation de l'image
            $image = 'https://spamtonprof.com/wp-content/uploads/lbc_images/' . $client->getImg_folder() . '/' . $images[($i % $nbImages) + 2];
            
            // recuperation de la commune
            $commune = $communes[$i % $nbCommunes];
            $nomCommune = $commune->getLibelle() . " " . $commune->getCode_postal();
            
            if ($lock) {
                // verouillage des communes prises dans les annonces
                $adTempo = new \spamtonprof\stp_api\AddsTempo(array(
                    "ref_compte" => $ref_compte,
                    "ref_commune" => $commune->getRef_commune()
                ));
                $adMg->add($adTempo);
            }
            
            $symbols = [
                '-',
                '_',
                '/',
                '=',
                '.',
                '*'
            ];
            $symbol = $symbols[rand(0, count($symbols) - 1)];
            $symbols_line = str_repeat($symbol, rand(10, 50));
            $texte->setTexte($symbols_line . PHP_EOL . PHP_EOL . $texte->getTexte() . PHP_EOL . PHP_EOL . $symbols_line);
            
            $ad = new \stdClass();
            $ad->title = $title;
            $ad->text = $texte;
            $ad->image = $image;
            $ad->commune = $nomCommune;
            $ad->commune = $nomCommune;
            $ads[] = $ad;
        }
        return ($ads);
    }

    // pour retouner la configuration d'un client leboncoin (le type de texte par defaut et le type de titre par defaut d'un client)
    public function getDefaultConf($refClient)
    {
        
        // on recupere le type titre
        $hasTypeTitleMg = new \spamtonprof\stp_api\HasTitleTypeManager();
        $typeTitreMg = new \spamtonprof\stp_api\TypeTitreManager();
        
        $hasTypeTitle = $hasTypeTitleMg->get(array(
            "ref_client_defaut" => $refClient
        ));
        
        $typeTitre = false;
        $messageTypeTitre = 'pas de ref type titre par defaut pour ce client (voir has_title_type)';
        if ($hasTypeTitle) {
            
            $typeTitre = $typeTitreMg->get(array(
                'ref_type' => $hasTypeTitle->getRef_type_titre()
            ));
            if (! $typeTitre) {
                $messageTypeTitre = 'pas type titre definie type_titre pour ce client (a ajouter)';
            }
        }
        
        // on recupere le type texte
        $hasTypeTexteMg = new \spamtonprof\stp_api\HasTextTypeManager();
        
        $typeTexteMg = new \spamtonprof\stp_api\TypeTexteManager();
        
        $hasTypeTexte = $hasTypeTexteMg->get(array(
            "ref_client_defaut" => $refClient
        ));
        
        $typeTexte = false;
        $messageTypeTexte = 'pas de ref type texte par defaut pour ce client (voir has_texte_type)';
        if ($hasTypeTexte) {
            
            $typeTexte = $typeTexteMg->get(array(
                'ref_type' => $hasTypeTexte->getRef_type()
            ));
            if (! $typeTexte) {
                $messageTypeTexte = 'pas type texte definie type_texte pour ce client (a ajouter)';
            }
        }
        
        // on recupere le client
        $clientMg = new \spamtonprof\stp_api\LbcClientManager();
        
        $client = $clientMg->get(array(
            'ref_client' => $refClient
        ));
        
        if ($typeTexte) {
            $messageTypeTexte = 'tout est ok pour le texte';
        }
        
        if ($typeTitre) {
            $messageTypeTitre = 'tout est ok pour le titre';
        }
        
        $conf = new \stdClass();
        $conf->typeTexte = $typeTexte;
        $conf->typeTitre = $typeTitre;
        $conf->messagetypeTexte = $messageTypeTexte;
        $conf->messagetypeTitre = $messageTypeTitre;
        
        $conf->client = $client;
        
        return ($conf);
    }

    // pour ajouter les titres lors de l'arrive d'un nouveau prof par exemple
    // ajouter type titre a la table type_titre
    // ajouter les titres a la table titres
    function addLbcTitle($fileName = 'titles.csv', $type)
    {
        $csv_path = ABSPATH . $fileName;
        
        // ajout du type titre si il existe pas
        $typeTitreMg = new \spamtonprof\stp_api\TypeTitreManager();
        
        $typeTitre = $typeTitreMg->get(array(
            "type" => $type
        ));
        
        if (! $typeTitre) {
            
            $typeTitre = $typeTitreMg->add(new \spamtonprof\stp_api\TypeTitre(array(
                'type' => $type
            )));
        }
        
        // ajout des titres
        $titleMg = new \spamtonprof\stp_api\LbcTitleManager();
        
        $rows = readCsv($csv_path);
        
        foreach ($rows as $row) {
            
            $titleMg->add(new \spamtonprof\stp_api\LbcTitle(array(
                "titre" => $row[0],
                "type_titre" => $typeTitre->getType(),
                "ref_type_titre" => $typeTitre->getRef_type()
            )));
        }
    }

    function addLbcEmails($ref_client, $fileName = 'emails.csv')
    {
        $csv_path = ABSPATH . $fileName;
        
        // ajout des titres
        $actMg = new \spamtonprof\stp_api\LbcAccountManager();
        
        $rows = readCsv($csv_path);
        
        foreach ($rows as $row) {
            
            echo ($row[0] . '<br>');
            
            $newAccount = new \spamtonprof\stp_api\LbcAccount();
            
            $newAccount->setRef_client($ref_client);
            $newAccount->setMail($row[0]);
            
            $newAccount = $actMg->add($newAccount);
        }
    }

    // pour repondre automatiquement aux premiers messages des prospects
    function sendAutomaticAnswer()
    {
        $gmailMg = new \spamtonprof\googleMg\GoogleManager('le.bureau.des.profs@gmail.com');
        
        $clientMg = new \spamtonprof\stp_api\LbcClientManager();
        $mailForLeadMg = new \spamtonprof\stp_api\MailForLeadManager();
        
        $gmailAccount = $this->gmailAccountMg->get("le.bureau.des.profs@gmail.com");
        
        $lastHistoryId = $gmailAccount->getLast_history_id();
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $now->sub(new \DateInterval("PT2H"));
        
        $now = $now->format('Y/m/d');
        
        $retour = $gmailMg->getNewMessages($lastHistoryId);
        
        $messages = $retour["messages"];
        
        $lastHistoryId = $retour["lastHistoryId"];
        
        $gmailAccount->setLast_history_id($lastHistoryId);
        $this->gmailAccountMg->updateHistoryId($gmailAccount);
        
        echo ("------ nb messages : " . count($messages) . " ----- <br><br><br>");
        
        foreach ($messages as $message) {
            
            // on regarde le titre du message si il commence par |--|ref_|--| c'est bon
            $subject = $gmailMg->getHeader($message, "Subject");
            $threadId = $message->threadId;
            $gmailId = $message->id;
            
            echo ($subject . ' -- ' . $threadId . '<br>');
            $matches = [];
            preg_match('/^\|--\|(\d*)\|--\|/', $subject, $matches);
            
            if (count($matches) != 2) {
                
                continue;
            }
            
            // on extrait la ref_message
            $refMessage = $matches[1];
            
            // on recupere le message dans la table message_prospect_lbc
            $messProspectMg = new \spamtonprof\stp_api\MessageProspectLbcManager();
            $msg = $messProspectMg->get(array(
                "ref_message" => $refMessage
            ));
            
            // on ne repond pas aux messages si il a deja eu une reponse
            if ($msg->getAnswered()) {
                
                // on attribue un libelle 'first_contact_done'
                $labelId = $gmailMg->getLabelsIds(array(
                    "first_contact_done"
                ));
                
                $gmailMg->modifyMessage($gmailId, $labelId, array());
                
                echo (utf8_encode('deja repondu <br>'));
                continue;
            }
            
            // a partir du message, on recupere ref_prospect_lbc
            $refProspect = $msg->getRef_prospect_lbc();
            
            // on fait une recherche de messages avec ref_prospect_lbc
            $msgs = $messProspectMg->getAll(array(
                'ref_prospect_lbc' => $refProspect,
                'answered' => true
            ));
            
            // si il n'y a pas de messages avec ref_prospect_lbc deja repondu alors on repond
            echo ("nb msg : " . count($msgs) . '<br>');
            if (count($msgs) > 0) {
                
                // on attribue un libelle 'first_contact_done'
                $labelId = $gmailMg->getLabelsIds(array(
                    "first_contact_done"
                ));
                
                $gmailMg->modifyMessage($gmailId, $labelId, array());
                
                continue;
            }
            
            // on recupere ref_compte_lbc a partir du message
            $refCompte = $msg->getRef_compte_lbc();
            
            // on recupere le compte lbc a partir de la ref_compte_lbc
            $act = $this->lbcAccountMg->get(array(
                'ref_compte' => $refCompte
            ));
            
            // puis on recupere le client, puis le message a envoyer
            $refClient = $act->getRef_client();
            
            $client = $clientMg->get(array(
                'ref_client' => $refClient
            ));
            
            // recuperation du message a envoyer
            $txtMg = new spamtonprof\stp_api\LbcTexteManager();
            
            $typeTxtMg = new \spamtonprof\stp_api\TypeTexteManager();
            $typeTxt = $typeTxtMg->get(array(
                'ref_type' => $client->getRef_reponse_lbc()
            ));
            
            $typeTxt = $typeTxt->getType();
            
            $txt = $txtMg->get(array(
                'type_random' => $typeTxt
            ));
            
            $txt = $txt->getTexte();
            
            $spamtonprofs = array(
                'sppamtonprof',
                'spaamtonprof',
                'spammtonprof',
                'spamttonprof',
                'spamtoonprof',
                'spamtonnprof',
                'spamtonpprof',
                'spamtonprrof',
                'spamtonproof',
                'pamtonprof',
                'spmtonprof',
                'spatonprof',
                'spamonprof',
                'spamtnprof',
                'spamtoprof',
                'spamtonrof'
            );
            
            $spamtonprof = $spamtonprofs[array_rand($spamtonprofs, 1)];
            
            $txt = str_replace(array(
                'spamtonprof',
                'sppamtonprof',
                'spamtonpprof'
            ), $spamtonprof, $txt);
            
            $body = str_replace('[prof_name]', $act->getPrenom(), $txt);
            
            // on envoie le message
            $gmailMg->sendMessage($body, 'Re: ' . $subject, 'mailsfromlbc@gmail.com', 'mailsfromlbc@gmail.com', 'le.bureau.des.profs@gmail.com', 'Cannelle Gaucher', $threadId);
            
            // on attribue un libelle 'answer'
            $labelId = $gmailMg->getLabelsIds(array(
                "bot_has_made_first_contact"
            ));
            
            $gmailMg->modifyMessage($gmailId, $labelId, array());
        }
    }
}