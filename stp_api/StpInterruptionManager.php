<?php
namespace spamtonprof\stp_api;

class StpInterruptionManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpInterruption $stpInterruption)
    {
        $q = $this->_db->prepare('insert into stp_interruption(debut, fin, prorate, ref_abonnement) values(:debut,:fin,:prorate,:ref_abonnement)');
        $q->bindValue(':debut', $stpInterruption->getDebut());
        $q->bindValue(':fin', $stpInterruption->getFin());
        $q->bindValue(':prorate', $stpInterruption->getProrate(), \PDO::PARAM_BOOL);
        $q->bindValue(':ref_abonnement', $stpInterruption->getRef_abonnement());
        $q->execute();

        $stpInterruption->setRef_interruption($this->_db->lastInsertId());
        return ($stpInterruption);
    }

    public function getAll($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists('debut', $info)) {

                $deb = $info['debut'];
                $q = $this->_db->prepare('select * from stp_interruption where debut = :debut');
                $q->bindValue(':debut', $deb);
                $q->execute();
            } else if (array_key_exists('fin', $info)) {

                $fin = $info['fin'];
                $q = $this->_db->prepare('select * from stp_interruption where fin = :fin');
                $q->bindValue(':fin', $fin);
                $q->execute();
            } else if (array_key_exists('prolongation', $info)) {

                $prolongation = $info['prolongation'];
                $q = $this->_db->prepare('select * from stp_interruption where prolongation = :prolongation');
                $q->bindValue(':prolongation', $prolongation);
                $q->execute();
            }
        }

        $q->execute();

        $interrups = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $interrup = new \spamtonprof\stp_api\StpInterruption($data);
            $interrups[] = $interrup;
        }
        return ($interrups);
    }

    public function updateFin(\spamtonprof\stp_api\StpInterruption $interrup)
    {
        $q = $this->_db->prepare("update stp_interruption set fin = :fin where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $interrup->getRef_abonnement());
        $q->bindValue(":fin", $interrup->getFin());
        $q->execute();
    }

    public function updateProlongation(\spamtonprof\stp_api\StpInterruption $interrup)
    {
        $q = $this->_db->prepare("update stp_interruption set prolongation = :prolongation where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $interrup->getRef_abonnement());
        $q->bindValue(":prolongation", $interrup->getProlongation());
        $q->execute();
    }

    public function get($info)
    {
        $q = null;
        if (is_array($info)) {
            if (array_key_exists('currentOrNextInterruption', $info)) {
                $refAbo = $info['currentOrNextInterruption'];
                $q = $this->_db->prepare('select * from stp_interruption where fin >= current_date and ref_abonnement = :ref_abonnement order by fin ');
                $q->bindValue(":ref_abonnement", $refAbo);
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $interrup = new \spamtonprof\stp_api\StpInterruption($data);
            return ($interrup);
        } else {
            return (false);
        }
    }
}
