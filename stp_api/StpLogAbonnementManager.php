<?php
namespace spamtonprof\stp_api;

class stpLogAbonnementManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpLogAbonnement $stpLogAbonnement)
    {
        $q = $this->_db->prepare('insert into stp_log_abonnement(ref_abonnement, ref_statut_abo, date) values( :ref_abonnement,:ref_statut_abo,:date)');
        $q->bindValue(':ref_abonnement', $stpLogAbonnement->getRef_abonnement());
        $q->bindValue(':ref_statut_abo', $stpLogAbonnement->getRef_statut_abo());
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $q->bindValue(':date', $now->format(PG_DATETIME_FORMAT));
        $q->execute();
        
        $stpLogAbonnement->setRef_log_abo($this->_db->lastInsertId());
        
        return ($stpLogAbonnement);
    }

    public function getDateDernierStatut(int $refAbonnement)
    {
        $q = $this->_db->prepare('select * from stp_log_abonnement where ref_abonnement = :ref_abonnement order by date desc');
        $q->bindValue(':ref_abonnement', $refAbonnement);
        $q->execute();
        
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        
        if ($data) {
            $logAbo = new \spamtonprof\stp_api\stpLogAbonnement($data);
            return($logAbo->getDate());
        } else {
            return (false);
        }
    }
}
