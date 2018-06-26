<?php
namespace spamtonprof\stp_api;

class stpStatutAbonnement implements \JsonSerializable
{

    protected $ref_statut_abonnement, $statut_abonnement;

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

    public function getRef_statut_abonnement()
    {
        return $this->ref_statut_abonnement;
    }

    public function setRef_statut_abonnement($ref_statut_abonnement)
    {
        $this->ref_statut_abonnement = $ref_statut_abonnement;
    }

    public function getStatut_abonnement()
    {
        return $this->statut_abonnement;
    }

    public function setStatut_abonnement($statut_abonnement)
    {
        $this->statut_abonnement = $statut_abonnement;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}