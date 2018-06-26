<?php
namespace spamtonprof\stp_api;

class stpMessageEleveManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpMessageEleve $stpMessageEleve)
    {
        $q = $this->_db->prepare('insert into stp_message_eleve(message, ref_abonnement, date_message, ref_message) values( :message,:ref_abonnement,:date_message,:ref_message)');
        $q->bindValue(':message', $stpMessageEleve->getMessage());
        $q->bindValue(':ref_abonnement', $stpMessageEleve->getRef_abonnement());
        $q->bindValue(':date_message', $stpMessageEleve->getDate_message());
        $q->bindValue(':ref_message', $stpMessageEleve->getRef_message());
        $q->execute();
        
        $stpMessageEleve->setRef_message($this->_db->lastInsertId());
        
        return ($stpMessageEleve);
    }
}
