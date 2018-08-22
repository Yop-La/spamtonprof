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
        $q = $this->_db->prepare('insert into stp_message_eleve(ref_abonnement, date_message, ref_gmail, mail_expe) values(:ref_abonnement, :date_message, :ref_gmail, :mail_expe)');
        $q->bindValue(':ref_abonnement', $StpMessageEleve->getRef_abonnement());
        $q->bindValue(':ref_gmail', $StpMessageEleve->getRef_gmail());
        $q->bindValue(':date_message', $StpMessageEleve->getDate_message());
        $q->bindValue(':mail_expe', $StpMessageEleve->getMail_expe());
        $q->execute();
        
        $StpMessageEleve->setRef_message($this->_db->lastInsertId());
        
        return ($StpMessageEleve);
    }
}
