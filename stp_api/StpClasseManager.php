<?php
namespace spamtonprof\stp_api;

use spamtonprof;

class StpClasseManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function getAll($info=null)
    {
        $classes = [];
        $q = null;
        
        if (is_array($info)) {
            
            if (array_key_exists("ref_profil", $info)) {
                
                $refProfil = $info["ref_profil"];
                
                $q = $this->_db->prepare('select * from stp_classe where ref_profil = :ref_profil');
                $q->bindValue(':ref_profil', $refProfil);
                $q->execute();
                
                
            }
        } else {
            
            $q = $this->_db->prepare('select * from stp_classe ');
            $q->execute();
            
            
            
        }
        
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            
            $classes[] = new spamtonprof\stp_api\StpClasse($data);
        }
        return ($classes);
    }

    public function get($info)
    {
        if (array_key_exists("ref_classe", $info)) {
            
            $refClasse = $info["ref_classe"];
            
            $q = $this->_db->prepare('select * from stp_classe where ref_classe = :ref_classe');
            $q->bindValue(':ref_classe', $refClasse);
            $q->execute();
            
            if ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
                
                return (new spamtonprof\stp_api\StpClasse($data));
            } else {
                return (false);
            }
        }
    }
}
