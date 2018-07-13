<?php
namespace spamtonprof\stp_api;

class LbcProcessManager
{

    public $slack, $gmailManager, $prospectLbcMg, $messProspectMg, $expeMg, $lbcAccountMg, $msgs, $errors, $gmailAccountMg, $gmailAccount;

    public function __construct()
    {
        $this->slack = new \spamtonprof\slack\Slack();
        
        $this->gmailManager = new \spamtonprof\gmailManager\GmailManager("mailsfromlbc@gmail.com");
        $this->prospectLbcMg = new \spamtonprof\stp_api\ProspectLbcManager();
        $this->messProspectMg = new \spamtonprof\stp_api\MessageProspectLbcManager();
        $this->lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $this->expeMg = new \spamtonprof\stp_api\ExpeLbcManager();
        $this->gmailAccountMg = new \spamtonprof\stp_api\StpGmailAccountManager();
        $this->messageTypeMg = new \spamtonprof\stp_api\LeadMessageTypeManager();
        
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
        
        $now = new \DateTime(null);
        
        $now = $now->format('Y/m/d');
        
        $messages = $this->gmailManager->getNewMessages("is:inbox after:" . $now, $lastHistoryId);
        
        echo("------  nb messages : " . count($messages) . " ----- <br>");
        
        $nbMessageToProcess = 100;
        $indexMessageProcessed = 0;
        
        foreach ($messages as $message) {
            
            $gmailId = $message->id;
            $historyId = $message->historyId;
            
            $from = $this->gmailManager->getHeader($message, "From");
            $snippet = $message->snippet;
            $subject = $this->gmailManager->getHeader($message, "Subject");
            $messageId = $this->gmailManager->getHeader($message, "Message-Id");
            $date = $this->gmailManager->getHeader($message, "Date");
            $body = $this->gmailManager->getBody($message);
            
            $dateReception = new \DateTime($date);
            $dateReception->setTimezone(new \DateTimeZone("Europe/Paris"));
            
            $messageType = 0;
            
            if (strpos($from, 'sender@mailer1.33mail.com') !== false) { // message du bon coin à priori
                
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
                    
                    // on ajoute à table lead messages
                    
                    $this->addNewLeadMessage($lbcProfil, $leadEmail, $dateReception, $gmailId, $subject, $messageType);
                    
                    // on attribue le libellé coresspondant à la catégorie
                    
                    $type = $this->messageTypeMg->get($messageType);
                    
                    $labelId = $this->gmailManager->getLabelsIds(array(
                        $type->getType()
                    ));
                    
                    $this->gmailManager->modifyMessage($gmailId, $labelId, array());
                    
                    // on envoie un message dans slacks
                    
                    $this->msgs[] = "------------------------";
                    $this->msgs[] = "Nouveau message ! gmailId : " . $gmailId;
                    $this->msgs[] = "date de réception : " . $dateReception->format(PG_DATETIME_FORMAT);
                    $this->msgs[] = "   ------   ";
                    $this->msgs[] = strip_tags($body);
                    
                    if (count($this->msgs) != 0) {
                        $this->slack->sendMessages($this->slack::LogLbc, $this->msgs);
                        $this->msgs = [];
                    }
                    
                    if (count($this->errors) != 0) {
                        
                        $this->errors = array_merge(array(
                            "--------- début des erreurs ---------"
                        ), $this->errors);
                        $this->errors[] = "--------- fin des erreurs ---------";
                        $this->slack->sendMessages($this->slack::LogLbc, $this->errors);
                        $this->errors = [];
                    }
                }
            } elseif (strpos($from, 'le.bureau.des.profs@gmail.com') !== false) {
                
                // last history id : 2254855 ( après avoir lu les 38 messages du bon coin )
                
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
                        
                        // attribuer un libellé pour dire que le message a été lu
                        
                        $labelId = $this->gmailManager->getLabelsIds(array(
                            "bot_read_it"
                        ));
                        
                        $this->gmailManager->modifyMessage($gmailId, $labelId, array());
                    }
                }
            }

            
            // on enregistre le dernier history id
            if ($lastHistoryId < $historyId) {
                
                echo(" lastHistoryId: " . $lastHistoryId . " et " . " historyId: " . $historyId  . "<br>");
                
                $lastHistoryId = $historyId;
                $this->gmailAccount->setLast_history_id($lastHistoryId);
                $this->gmailAccountMg->updateHistoryId($this->gmailAccount);
            }
            
            $indexMessageProcessed ++;
            if ($nbMessageToProcess == $indexMessageProcessed) {
                break;
            }
        }
    }

    public function processNewMessages()
    {
        
        // pour traiter les messages de leads si il y en a et les transférer à bureau des profs
        for ($i = 0; $i < 5; $i ++) {
            
            $this->processLeadMessage();
        }
        
        // pour envoyer au prospect les messages envoyés par le service prospection
        
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
        
        $this->gmailManager->sendMessage($body,$subject , "le.bureau.des.profs@gmail.com", $replyTo, "mailsfromlbc@gmail.com", "lbcBot");
        
        $message->setProcessed(true);
        $this->messProspectMg->updateProcessed($message);
    }

    private function addNewLeadMessage($emailAccountLbc, $contactLbc, $dateReception, $gmailId, $subject, $messageType)
    {
        
        // détermination du compte leboncoin associé au messsage
        $compteLbc = $this->lbcAccountMg->get(array(
            'mail' => $emailAccountLbc
        ));
        
        if (! $compteLbc) {
            $this->errors[] = "Impossible de trouver ce compte lbc dans la base : " . $emailAccountLbc;
            $this->errors[] = " ---- ";
            return;
        }
        
        // attribution d'un compte expe au compte leboncon si il n'existe pas
        if (is_null($compteLbc->getRef_expe())) {
            
            $refExpe = $this->expeMg->getRefExpe($emailAccountLbc);
            if (! $refExpe) {
                $this->errors[] = "impossible de trouver la ref expe de ce compte lbc : ";
                $this->errors[] = $emailAccountLbc;
                return;
            }
            
            $compteLbc->setRef_expe($refExpe);
            $this->lbcAccountMg->updateRefExpe($compteLbc);
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
            
            $expe = $compteLbc->getExpe();
            
            $smtpServer = $expe->getSmtpServer();
            
            $lead = $this->prospectLbcMg->get(array(
                "ref_prospect_lbc" => $message->getRef_prospect_lbc()
            ));
            
            $smtpServerMg = new SmtpServerManager();
            
            $subject = 'Re: ' . str_replace('leboncoin', 'lebonc...', $message->getSubject());
            $body = $message->getReply();
            
            // on supprime la partie écrite par 33mail.
            
            $to = $lead->getAdresse_mail(); // 'alex.guillemine@gmail.com'
            
            $pattern = '/(<div align="center">.*?<\/div><\/div>)|(This email was sent to the alias(.*?)[\r\n])/';
            $body = preg_replace_callback($pattern, function ($m) {
                return ("");
            }, $body);
            
            $rep = $smtpServer->sendEmail($subject, $to, $body, $compteLbc->getMail(), "Cannelle Gaucher", $html = true);
            
            if ($rep) {
                
                $message->setAnswered(true);
                $this->messProspectMg->updateAnswered($message);
                
                $msgs = array();
                $msgs[] = " ------------------------ ";
                $msgs[] = "Réponse automatique au mail : " . $message->getRef_message();
                $msgs[] = "Lead concerné : " . $lead->getAdresse_mail();
                $msgs[] = "Compte Lbc concerné et expediteur : " . $compteLbc->getMail();
                $msgs[] = "Sender : " . $smtpServer->getFrom();
                $msgs[] = "Réponse : ";
                $msgs[] = $subject;
                $msgs[] = strip_tags($body);
                $this->slack->sendMessages($this->slack::LogLbc, $msgs);
                
                $labelId = $this->gmailManager->getLabelsIds(array(
                    "Repondu"
                ));
                
                $this->gmailManager->modifyMessage($message->getGmail_id(), $labelId, array());
                $this->gmailManager->modifyMessage($message->getAnswer_gmail_id(), $labelId, array());
                
            }
        }
    }

    public function processLeadMessage()
    {
        $message = $this->messProspectMg->getLastLeadMessage();
        
        if ($message) {
            
            $this->forwadLeadMessages($message);
            
            // on attribue le libellé pour dire que le message a été transféré
            
            $labelId = $this->gmailManager->getLabelsIds(array(
                "forwarded"
            ));
            
            $this->gmailManager->modifyMessage($message->getGmail_id(), $labelId, array());
        }
    }

    public function testZone()
    {
        
     

//         $message = $this->gmailManager->getMessage("16481b67ba5ed8a5");
        
//         prettyPrint($message);
        
//         $histories = $this->gmailManager->listHistory(2235000, $historyTypes = "messageAdded", $labelId = "INBOX");
        
        $messages = $this->gmailManager->getNewMessages("is:inbox after:2018/07/09", 2234000);
        
        echo(count($messages));
        
    }
}