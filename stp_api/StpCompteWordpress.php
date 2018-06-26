<?php
namespace spamtonprof\stp_api;

class stpCompteWordpress implements \JsonSerializable
{

    protected $ref_wp, $ref_compte_famille;

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

    public function getRef_wp()
    {
        return $this->ref_wp;
    }

    public function setRef_wp($ref_wp)
    {
        $this->ref_wp = $ref_wp;
    }

    public function getRef_compte_famille()
    {
        return $this->ref_compte_famille;
    }

    public function setRef_compte_famille($ref_compte_famille)
    {
        $this->ref_compte_famille = $ref_compte_famille;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}