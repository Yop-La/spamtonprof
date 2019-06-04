<?php
namespace spamtonprof\stp_api;

class StpStageManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpStage $stpStage)
    {
        $q = $this->_db->prepare('insert into stp_stage(ref_eleve, ref_formule, ref_plan, ref_date_stage, date_inscription, remarque_inscription, ref_prof, ref_compte, subs_id, test) 
            values(:ref_eleve,:ref_formule,:ref_plan,:ref_date_stage,:date_inscription,:remarque_inscription,:ref_prof,:ref_compte,:subs_id, :test)');
        $q->bindValue(':ref_eleve', $stpStage->getRef_eleve());
        $q->bindValue(':ref_formule', $stpStage->getRef_formule());
        $q->bindValue(':ref_plan', $stpStage->getRef_plan());
        $q->bindValue(':ref_date_stage', $stpStage->getRef_date_stage());
        $q->bindValue(':date_inscription', $stpStage->getDate_inscription());
        $q->bindValue(':remarque_inscription', $stpStage->getRemarque_inscription());
        $q->bindValue(':ref_prof', $stpStage->getRef_prof());
        $q->bindValue(':ref_compte', $stpStage->getRef_compte());
        $q->bindValue(':subs_id', $stpStage->getSubs_id());
        $q->bindValue(':test', $stpStage->getTest(), \PDO::PARAM_BOOL);
        $q->execute();

        $stpStage->setRef_stage($this->_db->lastInsertId());

        return ($stpStage);
    }

    public function updateRefProche(\spamtonprof\stp_api\StpStage $stage)
    {
        $q = $this->_db->prepare("update stp_stage set ref_proche = :ref_proche where ref_stage = :ref_stage");
        $q->bindValue(":ref_stage", $stage->getRef_stage());
        $q->bindValue(":ref_proche", $stage->getRef_proche());
        $q->execute();
    }
}
