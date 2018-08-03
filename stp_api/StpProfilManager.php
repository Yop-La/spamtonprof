<?php
namespace spamtonprof\stp_api;

class stpProfilManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function getAll()
    {
        $profils = [];
        
        $q = $this->_db->prepare('select * from stp_profil');
        
        $q->execute();
        
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            
            $profils[] = new \spamtonprof\stp_api\stpProfil($data);
        }
        
        return ($profils);
    }
    
    
    public function get($info)
    {
        
        
        if(array_key_exists("ref_profil", $info)){
            
            $refProfil = $info["ref_profil"];
            
            $q = $this->_db->prepare('select * from stp_profil where ref_profil = :ref_profil');
            $q->bindValue(':ref_profil', $refProfil);
            $q->execute();
            
            if($data = $q->fetch(\PDO::FETCH_ASSOC)){
                
                return( new \spamtonprof\stp_api\stpProfil($data));
                
            }else{
                return(false);
            }
            
        }
        
    }
}
