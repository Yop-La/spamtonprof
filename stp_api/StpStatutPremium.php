<?php
namespace spamtonprof\stp_api;

class StpStatutPremium implements \JsonSerializable
{

    protected $statut_premium, $ref_statut_premium;

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

    public function getStatut_premium()
    {
        return $this->statut_premium;
    }

    public function setStatut_premium($statut_premium)
    {
        $this->statut_premium = $statut_premium;
    }

    public function getRef_statut_premium()
    {
        return $this->ref_statut_premium;
    }

    public function setRef_statut_premium($ref_statut_premium)
    {
        $this->ref_statut_premium = $ref_statut_premium;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}