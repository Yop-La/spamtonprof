<?php
namespace spamtonprof\stp_api;

use PDO;
use spamtonprof;

class ExpeLbcManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }
    
    /**
     * pour attribuer une ref expe automatiquement à un compte leboncoin qui n'en n'a pas encore
     */
    public function getRefExpe($lbcAccountAdress)
    {
        
        $q = $this->_db->prepare("select ref_expe from expe_lbc where '".$lbcAccountAdress."' like ANY (filtre) order by nb_message limit 1");
        $q->execute();
        
        $data = $q->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            return ($data["ref_expe"]);
        }else{
            return false;
        }
        
    }
    
    public function get($info){
        
        $data = false;
        if(array_key_exists("ref_expe", $info)){
            
            $refExepe = $info["ref_expe"];
            
            $q = $this->_db->prepare("select * from expe_lbc where ref_expe = :ref_expe");
            
            $q->bindValue(":ref_expe", $refExepe);
            
            $q->execute();
            
            $data = $q->fetch(PDO::FETCH_ASSOC);
            
        }
        
        if(!$data){
            return(false);
        }
        
        return(new spamtonprof\stp_api\ExpeLbc($data));
        
    }
    
    

}