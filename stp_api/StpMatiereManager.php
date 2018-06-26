<?php
namespace spamtonprof\stp_api;

class stpMatiereManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpMatiere $stpMatiere)
    {
        $q = $this->_db->prepare('insert into stp_matiere(ref_matiere, matiere) values( :ref_matiere,:matiere)');
        $q->bindValue(':ref_matiere', $stpMatiere->getRef_matiere());
        $q->bindValue(':matiere', $stpMatiere->getMatiere());
        $q->execute();
        
        $stpMatiere->setRef_matiere($this->_db->lastInsertId());
        
        return ($stpMatiere);
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
                return (new \spamtonprof\stp_api\stpMatiere($data));
            } else {
                return (false);
            }
        }
    }
}
