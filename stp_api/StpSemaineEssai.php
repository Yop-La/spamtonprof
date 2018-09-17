<?php
namespace spamtonprof\stp_api;

class StpSemaineEssai implements \JsonSerializable
{

    protected $ref_essai, $ref_abonnement, $debut, $fin, $ref_prof, $ref_statut_essai, $essai_rattrapage, $ref_raison_avortement;

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

    public function getRef_essai()
    {
        return $this->ref_essai;
    }

    public function setRef_essai($ref_essai)
    {
        $this->ref_essai = $ref_essai;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
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

    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    public function getRef_statut_essai()
    {
        return $this->ref_statut_essai;
    }

    public function setRef_statut_essai($ref_statut_essai)
    {
        $this->ref_statut_essai = $ref_statut_essai;
    }

    public function getEssai_rattrapage()
    {
        return $this->essai_rattrapage;
    }

    public function setEssai_rattrapage($essai_rattrapage)
    {
        $this->essai_rattrapage = $essai_rattrapage;
    }

    public function getRef_raison_avortement()
    {
        return $this->ref_raison_avortement;
    }

    public function setRef_raison_avortement($ref_raison_avortement)
    {
        $this->ref_raison_avortement = $ref_raison_avortement;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}