<?php
namespace spamtonprof\stp_api;

class StpStatutAbonnementManager
{

    const ACTIF = 1, ESSAI = 2, TERMINE = 3;
    
    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }
    
    public function get($info){
        
        $q = null;
        
        if(array_key_exists('ref_statut_abonnement', $info)){
            
            $refStatut = $info['ref_statut_abonnement'];
            
            $q = $this->_db->prepare('select * from stp_statut_abonnement where ref_statut_abonnement = :ref_statut_abonnement');
            $q->bindValue(':ref_statut_abonnement', $refStatut);
            $q->execute();
            
           
        }
        
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        
        if($data){
            return(new \spamtonprof\stp_api\StpStatutAbonnement($data));
        }else{
            return(false);
        }
        
    }

    
}
