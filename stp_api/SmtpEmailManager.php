<?php
namespace spamtonprof\stp_api;

use PDO;

class SmtpEmailManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }
    
    public function get($info){
        
        $data = false;
        $q;
        
        if(array_key_exists('ref_smtp_email', $info)){
            
            $refSmtpEmail = $info['ref_smtp_email'];
            
            $q = $this->_db->prepare('select * from smtp_email where ref_smtp_email = :ref_smtp_email');
            
            $q->bindValue(':ref_smtp_email', $refSmtpEmail);
            

        }
        
        $q->execute();
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if($data){
            return(new \spamtonprof\stp_api\SmtpEmail($data));
        }else{
            return(false);
        }
        
        
    }

}