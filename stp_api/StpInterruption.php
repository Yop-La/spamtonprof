<?php
namespace spamtonprof\stp_api;

class StpInterruption implements \JsonSerializable
{

    protected $ref_interruption, $debut, $fin, $prorate, $ref_abonnement;

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

    public function getDebut()
    {
        return $this->debut;
    }

    public function setDebut($debut)
    {
        $this->debut = $debut;
    }

    public function getFin()
    {
        return $this->fin;
    }

    public function setFin($fin)
    {
        $this->fin = $fin;
    }

    public function getProrate()
    {
        return $this->prorate;
    }

    public function setProrate($prorate)
    {
        $this->prorate = $prorate;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}