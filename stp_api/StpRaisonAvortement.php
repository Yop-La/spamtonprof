<?php
namespace spamtonprof\stp_api;

class stpRaisonAvortement implements \JsonSerializable
{

    protected $ref_raison_avortement, $raison_avortement;

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

    public function getRef_raison_avortement()
    {
        return $this->ref_raison_avortement;
    }

    public function setRef_raison_avortement($ref_raison_avortement)
    {
        $this->ref_raison_avortement = $ref_raison_avortement;
    }

    public function getRaison_avortement()
    {
        return $this->raison_avortement;
    }

    public function setRaison_avortement($raison_avortement)
    {
        $this->raison_avortement = $raison_avortement;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}