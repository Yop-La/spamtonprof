<?php
namespace spamtonprof\stp_api;

class StpDateFormuleManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpDateFormule $stpDateFormule)
    {
        $q = $this->_db->prepare('insert into stp_date_formule( libelle, date_debut, ref_formule, ref_plan) values( :libelle,:date_debut,:ref_formule, :ref_plan)');
        $q->bindValue(':libelle', $stpDateFormule->getLibelle());
        $q->bindValue(':date_debut', $stpDateFormule->getDate_debut());
        $q->bindValue(':ref_formule', $stpDateFormule->getRef_formule());
        $q->bindValue(':ref_plan', $stpDateFormule->getRef_plan());
        $q->execute();

        $stpDateFormule->setRef_date_formule($this->_db->lastInsertId());

        return ($stpDateFormule);
    }

    public function getAll($info = null)
    {
        $dates_formule = [];
        $q = null;

        if (is_array($info)) {

            if (array_key_exists("ref_formule", $info)) {

                $ref_formule = $info["ref_formule"];

                $q = $this->_db->prepare('select * from stp_date_formule where ref_formule = :ref_formule');

                $q->bindValue(":ref_formule", $ref_formule);

                $q->execute();
            }
        }

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $date_formule = new \spamtonprof\stp_api\StpDateFormule($data);

            $dates_formule[] = $date_formule;
        }
        return ($dates_formule);
    }
    
    public function get($info)
    {
        $q = null;
        if (is_array($info)) {
            
            if (array_key_exists("ref_date_formule", $info)) {
                
                $ref_date_formule = $info["ref_date_formule"];
                $q = $this->_db->prepare("select * from stp_date_formule where ref_date_formule = :ref_date_formule");
                $q->bindValue(":ref_date_formule", $ref_date_formule);
                
            } 
            
            $q->execute();
            
            $data = $q->fetch();
            
            if ($data) {
                
                $date_formule = new \spamtonprof\stp_api\StpDateFormule($data);

                
                return ($date_formule);
            } else {
                return (false);
            }
        }
    }
}
