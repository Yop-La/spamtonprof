<?php
namespace spamtonprof\stp_api;

class stpCompte implements \JsonSerializable
{

    protected $ref_compte, $date_creation, $ref_proche;

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

    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    public function getDate_creation()
    {
        return $this->date_creation;
    }

    public function setDate_creation(\DateTime $date_creation)
    {
        $this->date_creation = $date_creation;
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
}