<?php
namespace spamtonprof\stp_api;

class stpComposeManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpCompose $stpCompose)
    {
        $q = $this->_db->prepare('insert into stp_compose(ref_eleve, ref_compte) values( :ref_eleve,:ref_compte)');

        $q->bindValue(':ref_eleve', $stpCompose->getRef_eleve());
        $q->bindValue(':ref_compte', $stpCompose->getRef_compte());
        $q->execute();

        $stpCompose->setRef_stp_compose($this->_db->lastInsertId());
        
        return ($stpCompose);
    }
}
