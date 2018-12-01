<?php
namespace spamtonprof\stp_api;

use PDO;

class StpEleveManager
{

    private $_db;

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

    public function get($info, $constructor = false)
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
        } else if (array_key_exists("gr_id", $info)) {

            $grId = $info["gr_id"];

            $q = $this->_db->prepare('select * from stp_eleve where gr_id = :gr_id');
            $q->bindValue(':gr_id', $grId);
            $q->execute();
        }
        if (! $zapDataStep) {
            $data = $q->fetch(\PDO::FETCH_ASSOC);
        }
        if ($data) {

            $eleve = new \spamtonprof\stp_api\StpEleve($data);

            if ($constructor) {
                $constructor["objet"] = $eleve;
                $this->construct($constructor);
            }

            return ($eleve);
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
        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
        $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

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
                case "formules":

                    $constructorFormules = false;
                    if (array_key_exists("formules", $constructor)) {

                        $constructorFormules = $constructor["formules"];
                    }

                    $formules = $formuleMg->getAll(array(
                        'ref_eleve' => $eleve->getRef_eleve()
                    ), $constructorFormules);

                    $eleve->setFormules($formules);

                    break;
                case "abonnements":

                    $constructorAbos = false;
                    if (array_key_exists("abonnements", $constructor)) {

                        $constructorAbos = $constructor["abonnements"];
                    }

                    $abos = $aboMg->getAll(array(
                        'ref_eleve' => $eleve->getRef_eleve()
                    ), $constructorAbos);

                    $eleve->setAbos($abos);

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

    public function getAll($info, $eleveAsArray = false, $constructor = false)
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
        } else if (in_array('eleve_to_ad_in_gr', $info)) {

            $q = $this->_db->prepare("select * from stp_eleve where ref_eleve in (
                select ref_eleve from stp_abonnement 
                    where ref_prof is not null and ((ref_statut_abonnement = 2 and extract(day from now() - date_creation) <= 10) 
                        or ref_statut_abonnement = 1)) and same_email is false and gr_id is null limit 25");
            $q->execute();
        }

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            $eleve = new \spamtonprof\stp_api\StpEleve($data);
            if ($eleveAsArray) {
                $eleve = $eleve->toArray();
            } else if ($constructor) {
                $constructor["objet"] = $eleve;
                $this->construct($constructor);
            }
            $eleves[] = $eleve;
        }
        return ($eleves);
    }

    public function updateGrId(\spamtonprof\stp_api\StpEleve $eleve)
    {
        $q = $this->_db->prepare("update stp_eleve set gr_id = :gr_id where ref_eleve = :ref_eleve");
        $q->bindValue(":ref_eleve", $eleve->getRef_eleve());
        $q->bindValue(":gr_id", $eleve->getGr_id());
        $q->execute();
    }

    function toStpEleveGr(StpEleve $eleve, $update = false)
    {
        $slack = new \spamtonprof\slack\Slack();

        $niveau = $eleve->getNiveau()->getGr_id();
        $matieres = [];
        $statuts = [];
        $profs = [];

        $parentRequired = $eleve->getParent_required();
        $prenomProche = 'undefined';

        $abos = $eleve->getAbos();

        foreach ($abos as $abo) {

            $formule = $abo->getFormule();
            $matieresObj = $formule->getMatieres();
            $statut = $abo->getStatut()->getGr_id();

            $prof = $abo->getProf();
            $profGrId = $prof->getGr_id();

            foreach ($matieresObj as $matiere) {

                $matieres[] = $matiere->getGr_id();
            }

            if ($parentRequired) {
                $proche = $abo->getProche();
                if ($proche) {
                    $prenomProche = $abo->getProche()->getPrenom();
                } else {
                    $slack->sendMessages('log', array(
                        'cet eleve a parent required = true mais impossible de récup proche. ref_eleve :' . $eleve->getRef_eleve()
                    ));
                    continue;
                }
            }

            $statuts[] = $statut;
            $profs[] = $profGrId;
        }

        if ($update) {

            $params = '{
                "name": "' . $eleve->getPrenom() . '"
            }';
        } else {

            $params = '{
                "name": "' . $eleve->getPrenom() . '",
                "email": "' . $eleve->getEmail() . '",
                "campaign": {
                    "campaignId": "' . GrCampaignMg::STP_ELEVE . '"
                }
            }';
        }

        $params = json_decode($params);

        // ajout des tags et des champs
        $tags = [];
        $customFieldValues = [];

        foreach ($statuts as $statut) {
            $tag = new \stdClass();
            $tag->tagId = $statut;
            $tags[] = $tag;
        }

        foreach ($matieres as $matiere) {
            $tag = new \stdClass();
            $tag->tagId = $matiere;
            $tags[] = $tag;
        }

        foreach ($profs as $prof) {
            $tag = new \stdClass();
            $tag->tagId = $prof;
            $tags[] = $tag;
        }

        $tagNiveau = new \stdClass();
        $tagNiveau->tagId = $niveau;
        $tags[] = $tagNiveau;

        if ($parentRequired) {
            $tag->tagId = GrTagMg::PARENT_REQUIRED;
            $customFieldValue = new \stdClass();

            $customFieldValue->customFieldId = GrCustomFieldMg::PRENOM_PROCHE_ID;
            $customFieldValue->value = array(
                $prenomProche
            );

            $customFieldValues[] = $customFieldValue;
        }

        // ref eleve
        $customFieldValue = new \stdClass();

        $customFieldValue->customFieldId = GrCustomFieldMg::REF_ELEVE_ID;
        $customFieldValue->value = array(
            $eleve->getRef_eleve()
        );
        $customFieldValues[] = $customFieldValue;

        // jour de mise à jour
        $currentDayNumber = (date('z')) + 1;
        $customFieldValue = new \stdClass();
        $customFieldValue->customFieldId = GrCustomFieldMg::UPDATE_DAY_NUMBER;
        $customFieldValue->value = array(
            $currentDayNumber
        );
        $customFieldValues[] = $customFieldValue;

        $params->tags = $tags;
        $params->customFieldValues = $customFieldValues;

        return ($params);
    }
}
