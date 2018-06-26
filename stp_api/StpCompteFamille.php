<?php
namespace spamtonprof\stp_api;

class stpCompteFamille implements \JsonSerializable
{

    protected $ref_compte_famille, $date_creation, $ref_proche;

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

    public function getRef_compte_famille()
    {
        return $this->ref_compte_famille;
    }

    public function setRef_compte_famille($ref_compte_famille)
    {
        $this->ref_compte_famille = $ref_compte_famille;
    }

    public function getDate_creation()
    {
        return $this->date_creation;
    }

    public function setDate_creation($date_creation)
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