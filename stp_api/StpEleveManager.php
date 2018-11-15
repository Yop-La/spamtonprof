<?php
namespace spamtonprof\stp_api;

use PDO;

class StpEleveManager
{

    private $_db, $profilMg, $classeMg;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpEleve $StpEleve)
    {
        $q = $this->_db->prepare('insert into stp_eleve(email, prenom, ref_niveau, nom, telephone, same_email, parent_required,ref_compte, local) values(:email, :prenom, :ref_niveau, :nom, :telephone, :same_email, :parent_required,:ref_compte, :local)');
        $q->bindValue(':email', $StpEleve->getEmail());
        $q->bindValue(':prenom', $StpEleve->getPrenom());
        $q->bindValue(':ref_niveau', $StpEleve->getRef_niveau());
        $q->bindValue(':nom', $StpEleve->getNom());
        $q->bindValue(':telephone', $StpEleve->getTelephone());
        $q->bindValue(':same_email', $StpEleve->getSame_email(), \PDO::PARAM_BOOL);
        $q->bindValue(':parent_required', $StpEleve->getParent_required(), \PDO::PARAM_BOOL);
        $q->bindValue(':ref_compte', $StpEleve->getRef_compte());
        $q->bindValue(':local', $StpEleve->getLocal(), PDO::PARAM_BOOL);

        $q->execute();
        $StpEleve->setRef_eleve($this->_db->lastInsertId());
        return ($StpEleve);
    }

    public function updateRefCompteWp(StpEleve $eleve)
    {
        $refCompteWp = $eleve->getRef_compte_wp();

        $q = null;

        if (! LOCAL) {
            $q = $this->_db->prepare('update stp_eleve set ref_compte_wp = :ref_compte_wp where ref_eleve = :ref_eleve');
            $q->bindValue(':ref_compte_wp', $refCompteWp);
        } else {
            $q = $this->_db->prepare('update stp_eleve set ref_compte_wp_test = :ref_compte_wp_test where ref_eleve = :ref_eleve');
            $q->bindValue(':ref_compte_wp_test', $refCompteWp);
        }

        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();

        return ($eleve);
    }

    public function updateEmail(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set email = :email, same_email = :same_email where ref_eleve = :ref_eleve');
        $q->bindValue(':email', $eleve->getEmail());
        $q->bindValue(':same_email', $eleve->getSame_email(), PDO::PARAM_BOOL);
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();

        return ($eleve);
    }

    public function updateRefNiveau(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set ref_niveau = :ref_niveau where ref_eleve = :ref_eleve');
        $q->bindValue(':ref_niveau', $eleve->getRef_niveau());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();

        return ($eleve);
    }

    public function updateParentRequired(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set parent_required = :parent_required where ref_eleve = :ref_eleve');
        $q->bindValue(':parent_required', $eleve->getParent_required(), PDO::PARAM_BOOL);
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();

        return ($eleve);
    }

    public function updateSeqEmailParentEssai(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set seq_email_parent_essai = :seq_email_parent_essai where ref_eleve = :ref_eleve');
        $q->bindValue(':seq_email_parent_essai', $eleve->getSeq_email_parent_essai());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();

        return ($eleve);
    }

    public function updatePrenom(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set prenom = :prenom where ref_eleve = :ref_eleve');
        $q->bindValue(':prenom', $eleve->getPrenom());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();

        return ($eleve);
    }

    public function updateNom(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set nom = :nom where ref_eleve = :ref_eleve');
        $q->bindValue(':nom', $eleve->getNom());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();

        return ($eleve);
    }

    public function get($info)
    {
        $data = false;
        $zapDataStep = false;
        if (array_key_exists("email", $info)) {

            $email = $info["email"];

            $pos = strpos($email, '@');

            $radical = substr($email, 0, $pos);

            $radical = str_replace(".", "", $radical);

            $radical = implode('[\.]?', str_split($radical));

            $domain = substr($email, $pos);

            $email = $radical . $domain;

            $q = $this->_db->prepare('select * from stp_eleve where lower(email) ~ lower(:email)');
            $q->bindValue(':email', $email);
            $q->execute();
        } else if (array_key_exists("ref_compte_wp", $info)) {
            $refCompteWp = $info["ref_compte_wp"];

            if (LOCAL) {

                $q = $this->_db->prepare('select * from stp_eleve where ref_compte_wp_test = :ref_compte_wp');
                $q->bindValue(':ref_compte_wp', $refCompteWp);
                $q->execute();
                $data = $q->fetch(\PDO::FETCH_ASSOC);
            }

            if (! $data) {
                $q = $this->_db->prepare('select * from stp_eleve where ref_compte_wp = :ref_compte_wp');
                $q->bindValue(':ref_compte_wp', $refCompteWp);
                $q->execute();
            } else {
                $zapDataStep = true;
            }
        } else if (array_key_exists("ref_eleve", $info)) {

            $refEleve = $info["ref_eleve"];

            $q = $this->_db->prepare('select * from stp_eleve where ref_eleve = :ref_eleve');
            $q->bindValue(':ref_eleve', $refEleve);
            $q->execute();
        }
        if (! $zapDataStep) {
            $data = $q->fetch(\PDO::FETCH_ASSOC);
        }
        if ($data) {
            return (new \spamtonprof\stp_api\StpEleve($data));
        } else {
            return (false);
        }
    }

    public function cast(\spamtonprof\stp_api\StpEleve $object)
    {
        return ($object);
    }

    public function construct($constructor)
    {
        $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();

        $eleve = $this->cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {

                case "ref_niveau":
                    $niveau = $niveauMg->get(array(
                        'ref_niveau' => $eleve->getRef_niveau()
                    ));

                    $eleve->setNiveau($niveau);
                    break;
            }
        }
    }

    public function isInTrial($refEleve)
    {
        $q = $this->_db->prepare("select count(*) as nb_abo_essai from stp_abonnement where ref_statut_abonnement = 2 and ref_eleve = :ref_eleve");
        $q->bindValue(':ref_eleve', $refEleve);
        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        $nbEssai = $data["nb_abo_essai"];

        if ($nbEssai == 0) {
            return (false);
        } else {
            return (true);
        }
    }

    public function getAll($info, $eleveAsArray = false)
    {
        $eleves = [];
        $q = null;
        if (array_key_exists("ref_compte", $info)) {

            $refCompte = $info["ref_compte"];
            $q = $this->_db->prepare('select * from stp_eleve where ref_compte = :ref_compte ');
            $q->bindValue(":ref_compte", $refCompte);
            $q->execute();
        } else if (array_key_exists("email", $info)) {

            $email = $info["email"];

            $q = $this->_db->prepare('select * from stp_eleve where email like :email ');
            $q->bindValue(":email", '%' . $email . '%');
            $q->execute();
        } else if (array_key_exists("telephones", $info)) {

            $nums = formatNums($info["telephones"]);

            $nums = toSimilarTo($nums);

            $q = $this->_db->prepare("select * from stp_eleve where regexp_replace(telephone, '[^01234536789]', '','g') SIMILAR TO '" . $nums . "'");
            $q->execute();
        } else if (in_array("ref_niveau", $info) && in_array("parent_required", $info) && in_array(null, $info)) {

            $q = $this->_db->prepare("select * from stp_eleve where (ref_niveau is null or parent_required is null) and ref_classe is not null and ref_profil is not null limit 100");

            $q->execute();
        }

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            $eleve = new \spamtonprof\stp_api\StpEleve($data);
            if ($eleveAsArray) {
                $eleve = $eleve->toArray();
            }
            $eleves[] = $eleve;
        }
        return ($eleves);
    }

    /*
     * pour donner un niveau et un parent_required aux �l�ves inscrits
     * avant le changement de classe � niveau sur le site (avant ajout moteur de recherche pour essai)
     */
    public function setNiveauParentRequired()
    {
        $eleves = $this->getAll(array(
            'ref_niveau',
            'parent_required',
            null
        ));

        $classeMg = new \spamtonprof\stp_api\StpClasseManager();
        $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();

        $classeNotInNiveau = [
            "tstistl" => 15,
            "candidat-libre-bac" => 12,
            "diplome-universitaire" => 23,
            "pstistl" => 9,
            "iut" => 25
        ];

        foreach ($eleves as $eleve) {

            $classe = $classeMg->get(array(
                'ref_classe' => $eleve->getRef_classe()
            ));

            $niveau = $niveauMg->get(array(
                "sigle" => $classe->getClasse()
            ));

            if ($eleve->getRef_niveau() == 4) {
                $eleve->setParent_required(false);
            } else {
                $eleve->setParent_required(true);
            }
            $this->updateParentRequired($eleve);

            if ($niveau) {
                $eleve->setRef_niveau($niveau->getRef_niveau());
            } else {

                $eleve->setRef_niveau($classeNotInNiveau[$classe->getClasse()]);
            }
            $this->updateRefNiveau($eleve);
        }
    }
}
