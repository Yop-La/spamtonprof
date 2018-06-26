<?php
namespace spamtonprof\stp_api;

class stpStatutEssai implements \JsonSerializable
{

    protected $statut_essai, $ref_statut_essai;

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

    public function getStatut_essai()
    {
        return $this->statut_essai;
    }

    public function setStatut_essai($statut_essai)
    {
        $this->statut_essai = $statut_essai;
    }

    public function getRef_statut_essai()
    {
        return $this->ref_statut_essai;
    }

    public function setRef_statut_essai($ref_statut_essai)
    {
        $this->ref_statut_essai = $ref_statut_essai;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}