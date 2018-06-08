<?php
namespace spamtonprof\stp_api;

class LbcProcessManager
{

    public $slack, $gmailManager, $prospectLbcMg, $messProspectMg, $expeMg, $lbcAccountMg, $msgs, $errors;

    public function __construct()
    {
        $this->slack = new \spamtonprof\slack\Slack();
        
        $this->gmailManager = new \spamtonprof\gmailManager\GmailManager("mailsfromlbc@gmail.com");
        $this->prospectLbcMg = new \spamtonprof\stp_api\ProspectLbcManager();
        $this->messProspectMg = new \spamtonprof\stp_api\MessageProspectLbcManager();
        $this->lbcAccountMg = new \spamtonprof\stp_api\LbcAccountManager();
        $this->expeMg = new \spamtonprof\stp_api\ExpeLbcManager();
        $this->msgs = [];
        $this->errors = [];
    }

    public function __destruct()
    {
        if (count($this->msgs) != 0) {
            $this->slack->sendMessages($this->slack::LogLbc, $this->msgs);
        }
        
        if (count($this->errors) != 0) {
            
            $this->errors = array_merge(array(
                "--------- début des erreurs ---------"
            ), $this->errors);
            $this->errors[] = "--------- fin des erreurs ---------";
            $this->slack->sendMessages($this->slack::LogLbc, $this->errors);
        }
    }

    public function processNewLeadMessages()
    {
        $mess = $this->messProspectMg->getLastMessage();
        
        $after;
        if (! $mess) { // si pas de dernier message
            $after = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
            $after->sub(new \DateInterval('P300D'));
        } else {
            $after = $mess->getDate_reception();
        }
        
        $this->processLeadMessages($after);
    }

    public function processLeadMessages(\DateTime $after)
    {
        
        /*
         * on va itérer sur les mails de prospects.
         * il y a plusieurs types de mails de prospects :
         * type 1 :
         * - titre : Nouveau message concernant l'annonce -Re:
         * - critère de recherche : Nouveau message concernant l'annonce -Re:
         * type 2 :
         * - titre : " a répondu à votre annonce
         *
         */
        
        /* on commence par le type 1 */
        $this->processNewLeadType1Messages($after);
        
        /* puis le type 2 */
        $this->processNewLeadType2Messages($after);
    }

    public function playWithEmailTest()
    {
        $messageId = '1635b5d2e5d25f7f';
        $message = $this->gmailManager->getMessage($messageId, [
            'format' => 'full'
        ]);
        
        $email = new \spamtonprof\stp_api\Email(array(
            "message" => $message
        ), \spamtonprof\stp_api\EmailManager::lbcType1);
        
        echo ($email->getText());
        
        $this->slack->sendMessages($this->slack::LogLbc, array(
            $email->getText()
        ));
    }

    private function get_inner_html($node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }
        
        return $innerHTML;
    }

    public function processNewLeadType1Messages(\DateTime $after)
    {
        $timeStamp = $after->getTimestamp();
        
        $searchOperator = "Nouveau message concernant l'annonce -Re: after:" . $timeStamp;
        $messages = $this->gmailManager->listMessages($searchOperator);
        
        if (count($messages) == 0) {
            return;
        }
        
        $gmailId = $messages[count($messages) - 1]->getId();
        
        $isAlreadyProcess = $this->messProspectMg->get(array(
            "gmail_id" => $gmailId
        ));
        
        if ($isAlreadyProcess) {
            unset($messages[count($messages) - 1]);
        }
        
        foreach ($messages as $message) {
            
            $messageId = $message->getId();
            $message = $this->gmailManager->getMessage($messageId, [
                'format' => 'full'
            ]);
            
            $email = new \spamtonprof\stp_api\Email(array(
                "message" => $message
            ), \spamtonprof\stp_api\EmailManager::lbcType1);
            
            $matches = explode(";", $email->getSnippet());
            
            $emailAccountLbc = substr($matches[1], 0, - 4);
            $contactLbc = substr($matches[3], 0, - 4);
            $dateReception = $email->getDate_reception()->format(PG_DATETIME_FORMAT);
            $gmailId = $email->getRef_gmail();
            $subject = $email->getSubject();
            
            if ((strpos($matches[0], 'This email was sent to the alias') !== false)) {
                
                $this->msgs[] = "------------------------";
                $this->msgs[] = "Nouveau message " . $gmailId . "  de " . $contactLbc;
                $this->msgs[] = "pour le compte lbc : " . $emailAccountLbc;
                $this->msgs[] = "date de réception : " . $email->getDate_reception()->format(PG_DATETIME_FORMAT);
                $this->msgs[] = "   ------   ";
                $this->msgs[] = $email->getText();
                
                $this->addNewLeadMessage($emailAccountLbc, $contactLbc, $dateReception, $gmailId, $subject);
                
                $labelId = $this->gmailManager->getLabelsIds(array(
                    "bot_read_it"
                ));
                
                $this->gmailManager->modifyMessage($gmailId, $labelId, array());
            } else {
                
                $this->errors[] = "Impossible de traiter le message de prospect n° " . $gmailId;
                
                $this->errors[] = utf8_encode("Snippet liée à l'erreur : ") . $email->getSnippet();
            }
        }
    }

    public function processNewLeadType2Messages(\DateTime $after)
    {
        $timeStamp = $after->getTimestamp();
        
        $searchOperator = 'https://www.leboncoin.fr/phishing.htm -Re:' . ' after:' . $timeStamp;
        
        $messages = $this->gmailManager->listMessages($searchOperator);
        
        if (count($messages) == 0) {
            return;
        }
        
        $gmailId = $messages[count($messages) - 1]->getId();
        
        $isAlreadyProcess = $this->messProspectMg->get(array(
            "gmail_id" => $gmailId
        ));
        
        if ($isAlreadyProcess) {
            unset($messages[count($messages) - 1]);
        }
        
        foreach ($messages as $message) {
            
            $messageId = $message->getId();
            $message = $this->gmailManager->getMessage($messageId, [
                'format' => 'full'
            ]);
            
            $email = new \spamtonprof\stp_api\Email(array(
                "message" => $message
            ), \spamtonprof\stp_api\EmailManager::lbcType2);
            
            $matches = explode(";", $email->getSnippet());
            
            $dateReception = $email->getDate_reception()->format(PG_DATETIME_FORMAT);
            $gmailId = $email->getRef_gmail();
            $subject = $email->getSubject();
            
            if ((strpos($matches[0], 'This email was sent to the alias') !== false)) {
                
                $emailAccountLbc = substr($matches[1], 0, - 4);
                $contactLbc = substr($matches[3], 0, - 4);
                
                $this->msgs[] = "------------------------";
                $this->msgs[] = "Nouveau message " . $gmailId . "  de " . $contactLbc;
                $this->msgs[] = "pour le compte lbc : " . $emailAccountLbc;
                $this->msgs[] = "date de réception : " . $email->getDate_reception()->format(PG_DATETIME_FORMAT);
                $this->msgs[] = "   ------   ";
                $this->msgs[] = $email->getText();
                
                $this->addNewLeadMessage($emailAccountLbc, $contactLbc, $dateReception, $gmailId, $subject);
                
                $labelId = $this->gmailManager->getLabelsIds(array(
                    "bot_read_it"
                ));
                
                $this->gmailManager->modifyMessage($gmailId, $labelId, array());
            } else {
                
                $this->errors[] = "Impossible de traiter le message de prospect n° " . $gmailId;
                
                $this->errors[] = utf8_encode("Snippet liée à l'erreur : ") . $email->getSnippet();
                
                $this->errors[] = " ---- ";
            }
        }
    }

    private function addNewLeadMessage($emailAccountLbc, $contactLbc, $dateReception, $gmailId, $subject)
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
        $newProspect = false;
        if (! $prospectLbc) {
            
            $prospectLbc = new \spamtonprof\stp_api\ProspectLbc();
            $prospectLbc->setAdresse_mail($contactLbc);
            $prospectLbc = $this->prospectLbcMg->add($prospectLbc);
            
            $newProspect = true;
        }
        
        // enregistrement du messge du prospect
        $mess = new \spamtonprof\stp_api\MessageProspectLbc();
        
        $mess->setDate_reception($dateReception);
        $mess->setRef_compte_lbc($compteLbc->getRef_compte());
        $mess->setIs_sent(! $newProspect);
        $mess->setRef_prospect_lbc($prospectLbc->getRef_prospect_lbc());
        $mess->setGmail_id($gmailId);
        $mess->setSubject($subject);
        
        // return;
        
        $mess = $this->messProspectMg->add($mess);
    }

    public function answerToLeadMessages()
    {
        $message = $this->messProspectMg->getMessageToAnswer();
        
        if ($message) {
            
            $compteLbc = $this->lbcAccountMg->get(array(
                "ref_compte" => $message->getRef_compte_lbc()
            ));
            
            $expe = $compteLbc->getExpe();
            
            $mailForLead = $expe->getMailForLead();
            
            // prettyPrint($expe);
            
            $smtpServer = $expe->getSmtpServer();
            
            $lead = $this->prospectLbcMg->get(array(
                "ref_prospect_lbc" => $message->getRef_prospect_lbc()
            ));
            
            $smtpServerMg = new SmtpServerManager();
            
            $subject = 'Re: '.str_replace ('leboncoin', 'lebonc...',$message->getSubject());
            
            $rep = $smtpServer->sendEmail($subject, $lead->getAdresse_mail(), $mailForLead->getBody(), $compteLbc->getMail());
            if ($rep) {
                
                $message->setIs_sent(true);
                $this->messProspectMg->updateIsSent($message);
                
                $msgs = array();
                $msgs[] = " ------------------------ ";
                $msgs[] = "Réponse automatique au mail : " . $message->getGmail_id();
                $msgs[] = "Lead concerné : " . $lead->getAdresse_mail();
                $msgs[] = "Compte Lbc concerné et expediteur : " . $compteLbc->getMail();
                $msgs[] = "Sender : " . $smtpServer->getFrom();
                $msgs[] = "Réponse : ";
                $msgs[] = $subject;
                $msgs[] = $mailForLead->getBody();
                $this->slack->sendMessages($this->slack::LogLbc, $msgs);
            }
        }
    }
}