<?php
namespace spamtonprof\stp_api;

class StpRemarqueInscriptionManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpRemarqueInscription $StpRemarqueInscription)
    {
        $q = $this->_db->prepare('insert into stp_remarque_inscription(ref_abonnement, remarque, ref_matiere) values( :ref_abonnement, :remarque,:ref_matiere)');
        $q->bindValue(':ref_abonnement', $StpRemarqueInscription->getRef_abonnement());
        $q->bindValue(':remarque', $StpRemarqueInscription->getRemarque());
        $q->bindValue(':ref_matiere', $StpRemarqueInscription->getRef_matiere());
        $q->execute();

        $StpRemarqueInscription->setRef_remarque($this->_db->lastInsertId());

        return ($StpRemarqueInscription);
    }

    public function getAll($info, $constructor = false)
    {
        $q = null;
        $remarques = [];

        if (array_key_exists("ref_abonnement", $info)) {

            $refAbonnement = $info["ref_abonnement"];

            $q = $this->_db->prepare("select * from stp_remarque_inscription where ref_abonnement = :ref_abonnement");
            $q->bindValue(":ref_abonnement", $refAbonnement);
            $q->execute();
        }

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $remarque = new \spamtonprof\stp_api\StpRemarqueInscription($data);

            if ($constructor) {
                $constructor["objet"] = $remarque;
                $this->construct($constructor);
            }
            $remarques[] = $remarque;
        }
        return ($remarques);
    }

    public function construct($constructor)
    {
        $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();

        $remarque = $this->cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {

                case "ref_matiere":
                    $matiere = $matiereMg->get(array(
                        'ref_matiere' => $remarque->getRef_matiere()
                    ));

                    $remarque->setMatiere($matiere);
                    break;
            }
        }
    }

    public function cast(\spamtonprof\stp_api\StpRemarqueInscription $object)
    {
        return ($object);
    }

    public function delete(\spamtonprof\stp_api\StpRemarqueInscription $rem)
    {
        $q = $this->_db->prepare("delete from stp_remarque_inscription where ref_remarque =:ref_remarque");
        $q->bindValue(":ref_remarque", $rem->getRef_remarque());
        $q->execute();
    }

    public function deleteAll($info)
    {
        $q = $this->_db->prepare("delete from stp_remarque_inscription where ref_abonnement =:ref_abonnement");
        $q->bindValue(":ref_abonnement", $info);
        $q->execute();
    }
}
