<?php
namespace spamtonprof\stp_api;

class StpAssureManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpAssure $stpAssure)
    {
        $q = $this->_db->prepare('insert into stp_assure(ref_formule, ref_prof, ref_assure) values( :ref_formule,:ref_prof,:ref_assure)');
        $q->bindValue(':ref_formule', $stpAssure->getRef_formule());
        $q->bindValue(':ref_prof', $stpAssure->getRef_prof());
        $q->bindValue(':ref_assure', $stpAssure->getRef_assure());
        $q->execute();
        
        $stpAssure->setRef_assure($this->_db->lastInsertId());
        
        return ($stpAssure);
    }
}
