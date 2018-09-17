<?php
namespace spamtonprof\stp_api;

use PDO;

class MessageProspectLbcManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function getLastLeadMessage()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc where processed = false order by date_reception  limit 1");
        
        $q->execute();
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }

    public function getMessageToSend()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc where processed = true and answered = false and reply is not null order by date_reception limit 1;");
        
        $q->execute();
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }
    
    
    public function get($info)
    {
        $data = false;
        if (array_key_exists("gmail_id", $info)) {
            $gmailId = $info["gmail_id"];
            $q = $this->_db->prepare("select * from message_prospect_lbc where gmail_id =:gmail_id");
            $q->bindValue(":gmail_id", $gmailId);
            $q->execute();
            
            $data = $q->fetch(PDO::FETCH_ASSOC);
        }
        if (array_key_exists("ref_message", $info)) {
            $refMessage = $info["ref_message"];
            $q = $this->_db->prepare("select * from message_prospect_lbc where ref_message =:ref_message");
            $q->bindValue(":ref_message", $refMessage,PDO::PARAM_INT);
            $q->execute();
            
            $data = $q->fetch(PDO::FETCH_ASSOC);
        }
        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }
    
    public function updateAnswered(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc){
        
        $q = $this->_db->prepare("update message_prospect_lbc set answered = :answered where ref_message = :ref_message ");
        $q -> bindValue(":answered", $messageProspectLbc->getAnswered(), PDO::PARAM_BOOL);
        $q -> bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q -> execute();
        
    }
    
    public function updateProcessed(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc){
        
        $q = $this->_db->prepare("update message_prospect_lbc set processed = :processed where ref_message = :ref_message ");
        $q -> bindValue(":processed", $messageProspectLbc->getProcessed(), PDO::PARAM_BOOL);
        $q -> bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q -> execute();
        
    }
    
    public function updateReply(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc){
        
        $q = $this->_db->prepare("update message_prospect_lbc set reply = :reply where ref_message = :ref_message ");
        $q -> bindValue(":reply", $messageProspectLbc->getReply());
        $q -> bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q -> execute();
        
    }
    
    public function updateAnswerGmailId(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc){
        
        $q = $this->_db->prepare("update message_prospect_lbc set answer_gmail_id = :answer_gmail_id where ref_message = :ref_message ");
        $q -> bindValue(":answer_gmail_id", $messageProspectLbc->getAnswer_gmail_id());
        $q -> bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q -> execute();
        
    }


    public function add(MessageProspectLbc $message)
    {
        $q = $this->_db->prepare('INSERT INTO message_prospect_lbc(date_reception, ref_compte_lbc, ref_prospect_lbc, processed, gmail_id, subject, type, answered) VALUES(:date_reception, :ref_compte_lbc, :ref_prospect_lbc, :processed, :gmail_id, :subject, :type, :answered)');
        $q->bindValue(':date_reception', $message->getDate_reception()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_compte_lbc', $message->getRef_compte_lbc());
        $q->bindValue(':ref_prospect_lbc', $message->getRef_prospect_lbc());
        $q->bindValue(':processed', $message->getProcessed(), PDO::PARAM_BOOL);
        $q->bindValue(':gmail_id', $message->getGmail_id());
        $q->bindValue(':subject', $message->getSubject());
        $q->bindValue(':type', $message->getType(),PDO::PARAM_INT);
        $q->bindValue(':answered', $message->getAnswered(),PDO::PARAM_BOOL);
        
        $q->execute();
        
        $message->setRef_message($this->_db->lastInsertId());
        return ($message);
    }
}