<?php
namespace spamtonprof\stp_api;

use PDO;

class AbonnementManager
{

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }


    public function add(Abonnement $abonnement)
    {
        $q = $this->_db->prepare('INSERT INTO abonnement(ref_paypal_agreement,ref_stripe_subscription, ref_compte) VALUES(:ref_paypal_agreement, :ref_stripe_subscription, :ref_compte)');
        $q->bindValue(':ref_paypal_agreement', $abonnement->ref_paypal_agreement());
        $q->bindValue(':ref_compte', $abonnement->ref_compte());
        $q->bindValue(':ref_stripe_subscription', $abonnement->ref_stripe_subscription());
        $q->execute();
        
        $abonnement->setRef_abonnement($this->_db->lastInsertId());
        return ($abonnement);
    }

    public function count()
    {
        return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
    }
}