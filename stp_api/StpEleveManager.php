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
        $q = $this->_db->prepare('insert into stp_eleve(email, prenom, ref_classe, nom, telephone, same_email, ref_profil,ref_compte) values( :email,:prenom,:ref_classe,:nom,:telephone,  :same_email, :ref_profil, :ref_compte)');
        $q->bindValue(':email', $StpEleve->getEmail());
        $q->bindValue(':prenom', $StpEleve->getPrenom());
        $q->bindValue(':ref_classe', $StpEleve->getRef_classe());
        $q->bindValue(':nom', $StpEleve->getNom());
        $q->bindValue(':telephone', $StpEleve->getTelephone());
        $q->bindValue(':same_email', $StpEleve->getSame_email(), \PDO::PARAM_BOOL);
        $q->bindValue(':ref_profil', $StpEleve->getRef_profil());
        $q->bindValue(':ref_compte', $StpEleve->getRef_compte());
        
        $q->execute();
        $StpEleve->setRef_eleve($this->_db->lastInsertId());
        return ($StpEleve);
    }

    public function updateRefCompteWp(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set ref_compte_wp = :ref_compte_wp where ref_eleve = :ref_eleve');
        $q->bindValue(':ref_compte_wp', $eleve->getRef_compte_wp());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();
        
        return ($eleve);
    }

    public function updateEmail(StpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set email = :email, same_email = :same_email where ref_eleve = :ref_eleve');
        $q->bindValue(':email', $eleve->getEmail());
        $q->bindValue(':same_email', $eleve->getSame_email(),PDO::PARAM_BOOL);
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

    public function get($info)
    {
        $data = false;
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
        }
        
        if (array_key_exists("ref_compte_wp", $info)) {
            $refCompteWp = $info["ref_compte_wp"];
            $q = $this->_db->prepare('select * from stp_eleve where ref_compte_wp = :ref_compte_wp');
            $q->bindValue(':ref_compte_wp', $refCompteWp);
            $q->execute();
        }
        
        if (array_key_exists("ref_eleve", $info)) {
            
            $refEleve = $info["ref_eleve"];
            
            $q = $this->_db->prepare('select * from stp_eleve where ref_eleve = :ref_eleve');
            $q->bindValue(':ref_eleve', $refEleve);
            $q->execute();
        }
        
        if (array_key_exists("ref_compte_wp", $info)) {
            
            $refCompteWp = $info["ref_compte_wp"];
            
            $q = $this->_db->prepare('select * from stp_eleve where ref_compte_wp = :ref_compte_wp');
            $q->bindValue(':ref_compte_wp', $refCompteWp);
            $q->execute();
        }
        
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        
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
        $classeMg = new \spamtonprof\stp_api\StpClasseManager();
        $profilMg = new \spamtonprof\stp_api\StpProfilManager();
        
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
}
