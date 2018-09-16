<?php
namespace spamtonprof\stp_api;

class StpInterruption implements \JsonSerializable
{

    protected $ref_interruption, $ref_abonnement, $date_debut, $date_fin;

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

    public function getRef_interruption()
    {
        return $this->ref_interruption;
    }

    public function setRef_interruption($ref_interruption)
    {
        $this->ref_interruption = $ref_interruption;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
    }

    public function getDate_debut()
    {
        return $this->date_debut;
    }

    public function setDate_debut($date_debut)
    {
        $this->date_debut = $date_debut;
    }

    public function getDate_fin()
    {
        return $this->date_fin;
    }

    public function setDate_fin($date_fin)
    {
        $this->date_fin = $date_fin;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}