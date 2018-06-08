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

    public function getLastMessage()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc order by date_reception desc limit 1");
        
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
        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }
    
    public function updateIsSent(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc){
        
        $q = $this->_db->prepare("update message_prospect_lbc set is_sent = :is_sent where ref_message = :ref_message ");
        $q -> bindValue(":is_sent", $messageProspectLbc->getIs_sent(), PDO::PARAM_BOOL);
        $q -> bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q -> execute();
        
    }

    public function getMessageToAnswer()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc where is_sent = false order by date_reception  limit 1");
        
        $q->execute();
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if (! $data) {
            return false;
        }
        
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }

    public function add(MessageProspectLbc $message)
    {
        $q = $this->_db->prepare('INSERT INTO message_prospect_lbc(date_reception, ref_compte_lbc, ref_prospect_lbc, is_sent, gmail_id, subject) VALUES(:date_reception, :ref_compte_lbc, :ref_prospect_lbc, :is_sent, :gmail_id, :subject)');
        $q->bindValue(':date_reception', $message->getDate_reception()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_compte_lbc', $message->getRef_compte_lbc());
        $q->bindValue(':ref_prospect_lbc', $message->getRef_prospect_lbc());
        $q->bindValue(':is_sent', $message->getIs_sent(), PDO::PARAM_BOOL);
        $q->bindValue(':gmail_id', $message->getGmail_id());
        $q->bindValue(':subject', $message->getSubject());
        
        $q->execute();
        
        $message->setRef_message($this->_db->lastInsertId());
        return ($message);
    }
}