<?php
namespace spamtonprof\stp_api;

class stpClasseManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpClasse $stpClasse)
    {
        $q = $this->_db->prepare('insert into stp_classe(classe, ref_classe) values( :classe,:ref_classe)');
        $q->bindValue(':classe', $stpClasse->getClasse());
        $q->bindValue(':ref_classe', $stpClasse->getRef_classe());
        $q->execute();
        
        $stpClasse->setRef_classe($this->_db->lastInsertId());
        
        return ($stpClasse);
    }

    public function get($nomClasse)
    {
        $q = $this->_db->prepare('select * from stp_classe where classe like :classe');
        $q->bindValue(':classe', $nomClasse);
        $q->execute();
        
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            
            return (new \spamtonprof\stp_api\stpClasse($data));
        } else {
            return (false);
        }
    }
}
