<?php
namespace spamtonprof\stp_api;

class stpEleveManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpEleve $stpEleve)
    {
        $q = $this->_db->prepare('insert into stp_eleve(ref_compte_famille, email, prenom, ref_classe, nom, telephone) values(:ref_compte_famille, :email,:prenom,:ref_classe,:nom,:telephone)');
        $q->bindValue(':ref_compte_famille', $stpEleve->getRef_compte_famille());
        $q->bindValue(':email', $stpEleve->getEmail());
        $q->bindValue(':prenom', $stpEleve->getPrenom());
        $q->bindValue(':ref_classe', $stpEleve->getRef_classe());
        $q->bindValue(':nom', $stpEleve->getNom());
        $q->bindValue(':telephone', $stpEleve->getTelephone());
        $q->execute();
        
        $stpEleve->setRef_eleve($this->_db->lastInsertId());
        
        return ($stpEleve);
    }
}
