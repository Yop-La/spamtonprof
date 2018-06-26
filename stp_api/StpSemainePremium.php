<?php
namespace spamtonprof\stp_api;

class stpSemainePremium implements \JsonSerializable
{

    protected $ref_statut_premium, $ref_premium, $ref_abonnement, $ref_prof, $debut, $fin, $ref_raison_avortement, $date_avortement, $prof_paye, $probleme_paiement;

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

    public function getRef_statut_premium()
    {
        return $this->ref_statut_premium;
    }

    public function setRef_statut_premium($ref_statut_premium)
    {
        $this->ref_statut_premium = $ref_statut_premium;
    }

    public function getRef_premium()
    {
        return $this->ref_premium;
    }

    public function setRef_premium($ref_premium)
    {
        $this->ref_premium = $ref_premium;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
    }

    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
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

    public function getRef_raison_avortement()
    {
        return $this->ref_raison_avortement;
    }

    public function setRef_raison_avortement($ref_raison_avortement)
    {
        $this->ref_raison_avortement = $ref_raison_avortement;
    }

    public function getDate_avortement()
    {
        return $this->date_avortement;
    }

    public function setDate_avortement($date_avortement)
    {
        $this->date_avortement = $date_avortement;
    }

    public function getProf_paye()
    {
        return $this->prof_paye;
    }

    public function setProf_paye($prof_paye)
    {
        $this->prof_paye = $prof_paye;
    }

    public function getProbleme_paiement()
    {
        return $this->probleme_paiement;
    }

    public function setProbleme_paiement($probleme_paiement)
    {
        $this->probleme_paiement = $probleme_paiement;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}