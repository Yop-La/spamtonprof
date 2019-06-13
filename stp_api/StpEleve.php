<?php
namespace spamtonprof\stp_api;

class StpEleve implements \JsonSerializable
{

    protected $email, $prenom, $ref_classe, $nom, $telephone, $ref_eleve, $ref_compte_wp, $same_email, $ref_profil, $classe, $profil, $ref_compte, $seq_email_parent_essai, $hasToSendToEleve, $hasToSendToParent, $ref_niveau, $parent_required, $niveau, $gr_id, $formules, $abos, $update_gr, $add_to_gr;

    /**
     *
     * @return mixed
     */
    public function getFormules()
    {
        return $this->formules;
    }

    /**
     *
     * @return mixed
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     *
     * @return mixed
     */
    public function getProfil()
    {
        return $this->profil;
    }

    /**
     *
     * @return mixed
     */
    public function getAbos()
    {
        return $this->abos;
    }

    /**
     *
     * @param mixed $classe
     */
    public function setClasse($classe)
    {
        $this->classe = $classe;
    }

    /**
     * @return mixed
     */
    public function getUpdate_gr()
    {
        return $this->update_gr;
    }

    /**
     * @return mixed
     */
    public function getAdd_to_gr()
    {
        return $this->add_to_gr;
    }

    /**
     * @param mixed $update_gr
     */
    public function setUpdate_gr($update_gr)
    {
        $this->update_gr = $update_gr;
    }

    /**
     * @param mixed $add_to_gr
     */
    public function setAdd_to_gr($add_to_gr)
    {
        $this->add_to_gr = $add_to_gr;
    }

    /**
     *
     * @param mixed $profil
     */
    public function setProfil($profil)
    {
        $this->profil = $profil;
    }

    /**
     *
     * @param mixed $abos
     */
    public function setAbos($abos)
    {
        $this->abos = $abos;
    }

    /**
     *
     * @param mixed $formules
     */
    public function setFormules($formules)
    {
        $this->formules = $formules;
    }

    /**
     *
     * @return mixed
     */
    public function getGr_id()
    {
        return $this->gr_id;
    }

    /**
     *
     * @param mixed $gr_id
     */
    public function setGr_id($gr_id)
    {
        $this->gr_id = $gr_id;
    }



    /**
     *
     * @return mixed
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     *
     * @param mixed $niveau
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
    }

    /**
     *
     * @return mixed
     */
    public function getParent_required()
    {
        return $this->parent_required;
    }

    /**
     *
     * @param mixed $parent_required
     */
    public function setParent_required($parent_required)
    {
        $this->parent_required = $parent_required;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_niveau()
    {
        return $this->ref_niveau;
    }

    /**
     *
     * @param mixed $ref_niveau
     */
    public function setRef_niveau($ref_niveau)
    {
        $this->ref_niveau = $ref_niveau;
    }

    /**
     *
     * @return boolean
     */
    public function getHasToSendToEleve()
    {
        $this->hasToSendToEleve();
        return $this->hasToSendToEleve;
    }

    /**
     *
     * @return boolean
     */
    public function getHasToSendToParent()
    {
        $this->hasToSendToParent();
        return $this->hasToSendToParent;
    }

    /**
     *
     * @param boolean $hasToSendToEleve
     */
    public function setHasToSendToEleve($hasToSendToEleve)
    {
        $this->hasToSendToEleve = $hasToSendToEleve;
    }

    /**
     *
     * @param boolean $hasToSendToParent
     */
    public function setHasToSendToParent($hasToSendToParent)
    {
        $this->hasToSendToParent = $hasToSendToParent;
    }

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public function hasToSendToEleve()
    {
        if (is_null($this->getParent_required()) || is_null($this->getSame_email())) {
            return (null);
        }
        $this->setHasToSend();
        return ($this->hasToSendToEleve);
    }

    public function hasToSendToParent()
    {
        if (is_null($this->getParent_required()) || is_null($this->getSame_email())) {
            return (null);
        }
        $this->setHasToSend();
        return ($this->hasToSendToParent);
    }

    public function setHasToSend()
    {
        if (! $this->getParent_required()) {
            
            $this->hasToSendToEleve = true;
            $this->hasToSendToParent = false;
        } else {
            
            if ($this->getSame_email()) {
                
                $this->hasToSendToEleve = false;
                $this->hasToSendToParent = true;
            } else {
                
                $this->hasToSendToEleve = true;
                $this->hasToSendToParent = true;
            }
        }
    }

    public function toArray()
    {
        $retour = [];
        
        foreach ($this as $key => $value) {
            $retour[$key] = $value;
        }
        return ($retour);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_compte_wp()
    {
        return $this->ref_compte_wp;
    }

    /**
     *
     * @param mixed $ref_compte_wp
     */
    public function setRef_compte_wp($ref_compte_wp)
    {
        $this->ref_compte_wp = $ref_compte_wp;
    }

    public function getRef_classe()
    {
        return $this->ref_classe;
    }

    public function setRef_classe($ref_classe)
    {
        $this->ref_classe = $ref_classe;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    public function getRef_eleve()
    {
        return $this->ref_eleve;
    }

    public function setRef_eleve($ref_eleve)
    {
        $this->ref_eleve = $ref_eleve;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }

    /**
     *
     * @return mixed
     */
    public function getSame_email()
    {
        return $this->same_email;
    }

    /**
     *
     * @param mixed $same_email
     */
    public function setSame_email($same_email)
    {
        $this->same_email = $same_email;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_profil()
    {
        return $this->ref_profil;
    }

    /**
     *
     * @param mixed $ref_profil
     */
    public function setRef_profil($ref_profil)
    {
        $this->ref_profil = $ref_profil;
    }

    public static function cast(\spamtonprof\stp_api\StpEleve $eleve)
    {
        return ($eleve);
    }

    /**
     *
     * @return mixed
     */
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    /**
     *
     * @param mixed $ref_compte
     */
    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    /**
     *
     * @return mixed
     */
    public function getSeq_email_parent_essai()
    {
        return $this->seq_email_parent_essai;
    }

    /**
     *
     * @param mixed $seq_email_parent_essai
     */
    public function setSeq_email_parent_essai($seq_email_parent_essai)
    {
        $this->seq_email_parent_essai = $seq_email_parent_essai;
    }

    public function __toString()
    {
        $return = "Eleve: ";
        $return = $return . $this->prenom . " " . $this->nom . "\n";
        $return = $return . $this->email . "\n";
        
        $niveau = $this->niveau;
        if($niveau){
            $niveau = \spamtonprof\stp_api\StpNiveau::cast($niveau);
            $return = $return . $niveau->__toString() ;
        }
        
        return($return);
    }
}