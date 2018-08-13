<?php
namespace spamtonprof\stp_api;

use PDO;

class stpEleveManager
{

    private $_db, $profilMg, $classeMg;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpEleve $stpEleve)
    {
        $q = $this->_db->prepare('insert into stp_eleve(email, prenom, ref_classe, nom, telephone, same_email, ref_profil,ref_compte) values( :email,:prenom,:ref_classe,:nom,:telephone,  :same_email, :ref_profil, :ref_compte)');
        $q->bindValue(':email', $stpEleve->getEmail());
        $q->bindValue(':prenom', $stpEleve->getPrenom());
        $q->bindValue(':ref_classe', $stpEleve->getRef_classe());
        $q->bindValue(':nom', $stpEleve->getNom());
        $q->bindValue(':telephone', $stpEleve->getTelephone());
        $q->bindValue(':same_email', $stpEleve->getSame_email(), \PDO::PARAM_BOOL);
        $q->bindValue(':ref_profil', $stpEleve->getRef_profil());
        $q->bindValue(':ref_compte', $stpEleve->getRef_compte());
        
        $q->execute();
        $stpEleve->setRef_eleve($this->_db->lastInsertId());
        return ($stpEleve);
    }

    public function updateRefCompteWp(stpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set ref_compte_wp = :ref_compte_wp where ref_eleve = :ref_eleve');
        $q->bindValue(':ref_compte_wp', $eleve->getRef_compte_wp());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();
        
        return ($eleve);
    }

    public function updateSeqEmailParentEssai(stpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set seq_email_parent_essai = :seq_email_parent_essai where ref_eleve = :ref_eleve');
        $q->bindValue(':seq_email_parent_essai', $eleve->getSeq_email_parent_essai());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();
        
        return ($eleve);
    }

    public function get($info)
    {
        if (array_key_exists("email", $info)) {
            
            $email = $info["email"];
            
            $q = $this->_db->prepare('select * from stp_eleve where lower(email) like lower(:email)');
            $q->bindValue(':email', $email);
            $q->execute();
            
            $data = $q->fetch(\PDO::FETCH_ASSOC);
            
            if ($data) {
                return (new \spamtonprof\stp_api\stpEleve($data));
            } else {
                return (false);
            }
        }
        
        if (array_key_exists("ref_eleve", $info)) {
            
            $refEleve = $info["ref_eleve"];
            
            $q = $this->_db->prepare('select * from stp_eleve where ref_eleve = :ref_eleve');
            $q->bindValue(':ref_eleve', $refEleve);
            $q->execute();
            
            $data = $q->fetch(\PDO::FETCH_ASSOC);
            
            if ($data) {
                return (new \spamtonprof\stp_api\stpEleve($data));
            } else {
                return (false);
            }
        }
    }

    public function cast(\spamtonprof\stp_api\stpEleve $object)
    {
        return ($object);
    }

    public function construct($constructor)
    {
        $classeMg = new \spamtonprof\stp_api\stpClasseManager();
        $profilMg = new \spamtonprof\stp_api\stpProfilManager();
        
        $eleve = $this->cast($constructor["objet"]);
        
        $constructOrders = $constructor["construct"];
        
        foreach ($constructOrders as $constructOrder) {
            
            switch ($constructOrder) {
                case "ref_classe":
                    $classe = $classeMg->get(array(
                        'ref_classe' => $eleve->getRef_classe()
                    ));
                    
                    $eleve->setClasse($classe);
                    break;
                case "ref_profil":
                    $profil = $profilMg->get(array(
                        'ref_profil' => $eleve->getRef_profil()
                    ));
                    
                    $eleve->setProfil($profil);
                    break;
            }
        }
    }

    public function getAll($info)
    {
        $eleves = [];
        $q = null;
        if (array_key_exists("ref_compte", $info)) {
            
            $refCompte = $info["ref_compte"];
            $q = $this->_db->prepare('select * from stp_eleve where ref_compte = :ref_compte ');
            $q->bindValue(":ref_compte", $refCompte);
            $q->execute();

        }
        while($data = $q->fetch(PDO::FETCH_ASSOC)){
            $eleves[] = new \spamtonprof\stp_api\stpEleve($data);
        }
        return($eleves);
    }
}
