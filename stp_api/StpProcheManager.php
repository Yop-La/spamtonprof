<?php
namespace spamtonprof\stp_api;

class stpProcheManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpProche $stpProche)
    {
        $q = $this->_db->prepare('insert into stp_proche(email, prenom, nom, telephone) values( :email,:prenom,:nom,:telephone)');
        $q->bindValue(':email', $stpProche->getEmail());
        $q->bindValue(':prenom', $stpProche->getPrenom());
        $q->bindValue(':nom', $stpProche->getNom());
        $q->bindValue(':telephone', $stpProche->getTelephone());
        $q->execute();
        
        $stpProche->setRef_proche($this->_db->lastInsertId());
        
        return ($stpProche);
    }
}
