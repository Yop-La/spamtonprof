<?php
namespace spamtonprof\stp_api;

class stpLogAbonnement implements \JsonSerializable
{

    protected $ref_abonnement, $ref_log_abo, $ref_statut_abo, $date;

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

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
    }

    public function getRef_log_abo()
    {
        return $this->ref_log_abo;
    }

    public function setRef_log_abo($ref_log_abo)
    {
        $this->ref_log_abo = $ref_log_abo;
    }

    public function getRef_statut_abo()
    {
        return $this->ref_statut_abo;
    }

    public function setRef_statut_abo($ref_statut_abo)
    {
        $this->ref_statut_abo = $ref_statut_abo;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}