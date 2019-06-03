<?php
namespace spamtonprof\stp_api;

class StpCompteManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpCompte $StpCompte)
    {
        $q = $this->_db->prepare('insert into stp_compte(date_creation, ref_proche) values( :date_creation,:ref_proche)');
        $q->bindValue(':date_creation', $StpCompte->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_proche', $StpCompte->getRef_proche());
        $q->execute();

        $StpCompte->setRef_compte($this->_db->lastInsertId());

        return ($StpCompte);
    }

    public function delete($refCompte)
    {
        $q = $this->_db->prepare('select * from stp_abonnement where ref_compte = :ref_compte; ');
        $q->bindValue(':ref_compte', $refCompte);
        $q->execute();

        $refAbos = [];
        $refEleves = [];
        $refProches = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $refAbos[] = $data["ref_abonnement"];
            $refEleves[] = $data["ref_eleve"];
            $refProches[] = $data["ref_proche"];
        }

        $refAbosQr = implode(',', array_fill(0, count($refAbos), '?'));
        $refElevesQr = implode(',', array_fill(0, count($refEleves), '?'));
        $refProchesQr = implode(',', array_fill(0, count($refProches), '?'));

        $q = $this->_db->prepare('delete from stp_message_eleve where ref_abonnement in (' . $refAbosQr . ');');
        $q->execute($refAbos);

        $q = $this->_db->prepare('delete from stp_log_abonnement where ref_abonnement in (' . $refAbosQr . ');');
        $q->execute($refAbos);

        $q = $this->_db->prepare('delete from stp_remarque_inscription where ref_abonnement in (' . $refAbosQr . ');');
        $q->execute($refAbos);

        $q = $this->_db->prepare('delete from stp_abonnement where ref_abonnement in (' . $refAbosQr . ');');
        $q->execute($refAbos);

        $q = $this->_db->prepare('delete from stp_eleve where ref_eleve in (' . $refElevesQr . ')');
        $q->execute($refEleves);

        $q = $this->_db->prepare('delete from stp_compte where ref_compte = :ref_compte; ');
        $q->bindValue(':ref_compte', $refCompte);
        $q->execute();

        $q = $this->_db->prepare('delete from stp_proche where ref_proche in (' . $refProchesQr . ')');
        $q->execute($refProches);

        print_r($this->_db->errorInfo());
    }

    /*
     * retourne le num�ro de list d'essai parent occup� ( ie dont l'abonnement associ� est en essai ) du compte $refCompte
     * ou 0 si il n'y aucun abonnement en essai
     *
     */
    public function getNotFreeParentTrialList($refCompte)
    {
        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
        $statutEssai = new \spamtonprof\stp_api\StpStatutEssai();

        $abonnementsCompte = $abonnementMg->getAll(array(
            "ref_compte" => $refCompte
        ));

        foreach ($abonnementsCompte as $abonnementCompte) {

            if ($abonnementCompte->getRef_statut_abonnement() == \spamtonprof\stp_api\StpStatutAbonnementManager::ESSAI) {

                $eleve = $eleveMg->get(array(
                    "ref_eleve" => $abonnementCompte->getRef_eleve()
                ));

                if ($eleve->getSeq_email_parent_essai() != 0) { // si 0 alors parent pas encore dans liste
                    return ($eleve->getSeq_email_parent_essai());
                }
            }
        }
        return (0);
    }

    public function get($info)
    {
        $q = null;
        if (array_key_exists('ref_compte_wp', $info)) {

            $refCompteWp = $info["ref_compte_wp"];

            $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
            $procheMg = new \spamtonprof\stp_api\StpProcheManager();

            $eleve = $eleveMg->get(array(
                "ref_compte_wp" => $refCompteWp
            ));
            $compte1 = false;
            if ($eleve) {
                $compte1 = $this->get(array(
                    "ref_compte" => $eleve->getRef_compte()
                ));
            }

            $compte2 = false;
            $proche = $procheMg->get(array(
                "ref_compte_wp" => $refCompteWp
            ));
            if ($proche) {
                $compte2 = $this->get(array(
                    "ref_proche" => $proche->getRef_proche()
                ));
            }


            if ($compte1) {
                return ($compte1);
            }
            if ($compte2) {
                return ($compte2);
            }

            return (false);
        }

        if (array_key_exists("ref_compte", $info)) {

            $refCompte = $info["ref_compte"];

            $q = $this->_db->prepare('select * from stp_compte where ref_compte = :ref_compte');
            $q->bindValue(':ref_compte', $refCompte);
            $q->execute();
        }

        if (array_key_exists("ref_proche", $info)) {

            $refProche = $info["ref_proche"];

            $q = $this->_db->prepare('select * from stp_compte where ref_proche = :ref_proche');
            $q->bindValue(':ref_proche', $refProche);
            $q->execute();
        }

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\StpCompte($data));
        } else {
            return (false);
        }
    }

    public function updateStripeClient(\spamtonprof\stp_api\StpCompte $compte)
    {
        $q = $this->_db->prepare("update stp_compte set stripe_client = :stripe_client where ref_compte = :ref_compte");
        $q->bindValue(":ref_compte", $compte->getRef_compte());
        $q->bindValue(":stripe_client", $compte->getStripe_client());
        $q->execute();
    }
}
