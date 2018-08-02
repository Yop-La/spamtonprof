<?php
namespace spamtonprof\stp_api;

class stpRemarqueInscriptionManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpRemarqueInscription $stpRemarqueInscription)
    {
        $q = $this->_db->prepare('insert into stp_remarque_inscription(ref_abonnement, chapitre, difficulte, note, ref_matiere) values( :ref_abonnement,:chapitre,:difficulte,:note,:ref_matiere)');
        $q->bindValue(':ref_abonnement', $stpRemarqueInscription->getRef_abonnement());
        $q->bindValue(':chapitre', $stpRemarqueInscription->getChapitre());
        $q->bindValue(':difficulte', $stpRemarqueInscription->getDifficulte());
        $q->bindValue(':note', $stpRemarqueInscription->getNote());
        $q->bindValue(':ref_matiere', $stpRemarqueInscription->getRef_matiere());
        $q->execute();
        
        $stpRemarqueInscription->setRef_remarque($this->_db->lastInsertId());
        
        return ($stpRemarqueInscription);
    }
}
