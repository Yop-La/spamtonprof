<?php
namespace spamtonprof\stp_api;

class StpMatiereManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpMatiere $StpMatiere)
    {
        $q = $this->_db->prepare('insert into stp_matiere(ref_matiere, matiere) values( :ref_matiere,:matiere)');
        $q->bindValue(':ref_matiere', $StpMatiere->getRef_matiere());
        $q->bindValue(':matiere', $StpMatiere->getMatiere());
        $q->execute();
        
        $StpMatiere->setRef_matiere($this->_db->lastInsertId());
        
        return ($StpMatiere);
    }

    public function get($info)
    {
        if (array_key_exists('matiere', $info)) {
            
            $matiere = $info['matiere'];
            
            $q = $this->_db->prepare('select * from stp_matiere where matiere like :matiere');
            $q->bindValue(':matiere', $matiere);
            $q->execute();
            
            $data = $q->fetch(\PDO::FETCH_ASSOC);
            
            if ($data) {
                return (new \spamtonprof\stp_api\StpMatiere($data));
            } else {
                return (false);
            }
        }
        
        if (array_key_exists('ref_matiere', $info)) {
            
            $refMatiere = $info['ref_matiere'];
            
            $q = $this->_db->prepare('select * from stp_matiere where ref_matiere = :ref_matiere');
            $q->bindValue(':ref_matiere', $refMatiere);
            $q->execute();
            
            $data = $q->fetch(\PDO::FETCH_ASSOC);
            
            if ($data) {
                return (new \spamtonprof\stp_api\StpMatiere($data));
            } else {
                return (false);
            }
        }
    }
}
