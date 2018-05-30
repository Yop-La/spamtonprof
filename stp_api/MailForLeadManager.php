<?php
namespace spamtonprof\stp_api;

use PDO;

class MailForLeadManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }
    
    public function get($info){
        
        if(array_key_exists("ref_mail_for_lead", $info)){
            $refMAilForLead = $info["ref_mail_for_lead"];
            $q = $this->_db->prepare("select * from mail_for_lead where ref_mail_for_lead = :ref_mail_for_lead");
            $q->bindValue(":ref_mail_for_lead", $refMAilForLead);
            $q->execute();
            
            $data = $q->fetch(PDO::FETCH_ASSOC);
            
        }
        
        if(!$data){
            return false;
        }
        
        return(new \spamtonprof\stp_api\MailForLead($data));
        
    }

}