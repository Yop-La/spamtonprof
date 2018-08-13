<?php
namespace spamtonprof\stp_api;



class stpMatiere implements \JsonSerializable
{

    const MATHS = 1, FRANCAIS = 2, PHYSIQUE = 3;
    
    protected $ref_matiere, $matiere;

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