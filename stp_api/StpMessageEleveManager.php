<?php
namespace spamtonprof\stp_api;

class StpMessageEleveManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpMessageEleve $StpMessageEleve)
    {
        $q = $this->_db->prepare('insert into stp_message_eleve(message, ref_abonnement, date_message, ref_message) values( :message,:ref_abonnement,:date_message,:ref_message)');
        $q->bindValue(':message', $StpMessageEleve->getMessage());
        $q->bindValue(':ref_abonnement', $StpMessageEleve->getRef_abonnement());
        $q->bindValue(':date_message', $StpMessageEleve->getDate_message());
        $q->bindValue(':ref_message', $StpMessageEleve->getRef_message());
        $q->execute();
        
        $StpMessageEleve->setRef_message($this->_db->lastInsertId());
        
        return ($StpMessageEleve);
    }
}
