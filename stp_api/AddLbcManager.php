<?php
namespace spamtonprof\stp_api;

use PDO;

class AddLbcManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }
    
    public function delete($info){
        
        if(array_key_exists("ref_compte", $info)){
            
            $q = $this->_db->prepare("delete from adds_lbc where ref_compte = :ref_compte");
            $q->execute(array("ref_compte" => $info["ref_compte"]));
            
        }
        
        
    }
    
    


}