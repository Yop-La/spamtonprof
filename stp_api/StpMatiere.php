<?php
namespace spamtonprof\stp_api;

class StpMatiere implements \JsonSerializable
{

    const MATHS = 1, FRANCAIS = 2, PHYSIQUE = 3;

    protected $ref_matiere, $matiere, $matiere_complet, $gr_id;

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
    public function getMatiere_complet()
    {
        return $this->matiere_complet;
    }

    /**
     *
     * @param mixed $matiere_complet
     */
    public function setMatiere_complet($matiere_complet)
    {
        $this->matiere_complet = $matiere_complet;
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

    public function getRef_matiere()
    {
        return $this->ref_matiere;
    }

    public function setRef_matiere($ref_matiere)
    {
        $this->ref_matiere = $ref_matiere;
    }

    public function getMatiere()
    {
        return $this->matiere;
    }

    public function setMatiere($matiere)
    {
        $this->matiere = $matiere;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}