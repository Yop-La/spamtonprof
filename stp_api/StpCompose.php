<?php
namespace spamtonprof\stp_api;

class stpCompose implements \JsonSerializable
{

    protected $ref_stp_compose, $ref_eleve, $ref_compte;

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

    public function getRef_stp_compose()
    {
        return $this->ref_stp_compose;
    }

    public function setRef_stp_compose($ref_stp_compose)
    {
        $this->ref_stp_compose = $ref_stp_compose;
    }

    public function getRef_eleve()
    {
        return $this->ref_eleve;
    }

    public function setRef_eleve($ref_eleve)
    {
        $this->ref_eleve = $ref_eleve;
    }

    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}