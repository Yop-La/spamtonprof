<?php
namespace spamtonprof\stp_api;

use PDO;

class StpFormuleManager

{

    private $_db;

    // Instance de PDO
    public function __construct()
    
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }
    
    public function add(\spamtonprof\stp_api\StpFormule $formule){
        
        
        $q = $this->_db->prepare("insert into stp_formule(formule) values(:formule);");
        
        $q -> bindValue(":formule", $formule->getFormule());

        
        $q->execute();
        
        $formule->setRef_formule($this->_db->lastInsertId());
        
        return($formule);
        
    }
    
    public function get($info){
        
        if(is_array($info)){
            
            if(array_key_exists("formule", $info)){
                
                $nomFormule = $info["formule"];

                $q = $this->_db->prepare("select * from stp_formule where formule = :formule");
                
                $q -> bindValue(":formule", $nomFormule);
                
                $q->execute();
                
                $data = $q->fetch(PDO::FETCH_ASSOC);
                
                if($data){
                    return(new \spamtonprof\stp_api\StpFormule($data));
                }else{
                    return(false);
                }
                
            }
            
            
        }
        
    }

}