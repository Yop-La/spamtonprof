<?php
namespace spamtonprof\stp_api;

use PDO;

class SmtpServerManager

{
    
    const soutienMathsPhysiqueAtThomasCoursFr = 'soutien-maths-physique@thomas-cours.fr',
    mathsPhysiqueAtThomasCoursFr = 'maths-physique@thomas-cours.fr',
    francaisAtThomasCoursFr = 'francais@thomas-cours.fr',
    coursparmailAtthomasCoursFr = 'coursparmail@thomas-cours.fr';

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
     
    }
    
    public function get($info){
        
        $data = false;
        if(array_key_exists("mail", $info)){
            $mail = $info["mail"];
            $q = $this->_db->prepare("select * from smtp_server where lower(username) = lower(:username)");
            $q->bindValue(":username", $mail);
            $q->execute();
            
            $data = $q->fetch(PDO::FETCH_ASSOC);
            
        }
        if(array_key_exists("ref_smtp_server", $info)){
            $refSmtpServer = $info["ref_smtp_server"];
            $q = $this->_db->prepare("select * from smtp_server where ref_smtp_server = :ref_smtp_server");
            $q->bindValue(":ref_smtp_server", $refSmtpServer);
            $q->execute();
            
            $data = $q->fetch(PDO::FETCH_ASSOC);
            
        }
        
        if(!$data){
            return false;
        }
        
        return(new \spamtonprof\stp_api\SmtpServer($data));
        
        
    }

}