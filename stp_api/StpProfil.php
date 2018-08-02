<?php
namespace spamtonprof\stp_api;

class stpProfil implements \JsonSerializable
{

    protected $ref_profil, $profil;

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

    public function getRef_profil()
    {
        return $this->ref_profil;
    }

    public function setRef_profil($ref_profil)
    {
        $this->ref_profil = $ref_profil;
    }

    public function getProfil()
    {
        return $this->profil;
    }

    public function setProfil($profil)
    {
        $this->profil = $profil;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}