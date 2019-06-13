<?php
namespace spamtonprof\stp_api;

class StpProche implements \JsonSerializable
{

    protected $email, $prenom, $nom, $telephone, $ref_proche, $ref_compte_wp, $statut_proche, $gr_id, $update_gr, $add_to_gr;


    
    /**
     * @return mixed
     */
    public function getGr_id()
    {
        return $this->gr_id;
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
     * @param mixed $gr_id
     */
    public function setGr_id($gr_id)
    {
        $this->gr_id = $gr_id;
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

    public function getRef_proche()
    {
        return $this->ref_proche;
    }

    public function setRef_proche($ref_proche)
    {
        $this->ref_proche = $ref_proche;
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
    public function getStatut_proche()
    {
        return $this->statut_proche;
    }

    /**
     *
     * @param mixed $statut_proche
     */
    public function setStatut_proche($statut_proche)
    {
        $this->statut_proche = $statut_proche;
    }

    public function toArray()
    {
        $retour = [];

        foreach ($this as $key => $value) {
            $retour[$key] = $value;
        }
        return ($retour);
    }

    public static function cast($proche): \spamtonprof\stp_api\StpProche
    {
        return ($proche);
    }
    
    public function __toString()
    {
        $return = "Proche: ";
        $return = $return . $this->prenom . " " . $this->nom . "\n";
        $return = $return . $this->email . "\n";
        return($return);
    }
}