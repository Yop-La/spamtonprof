<?php
namespace spamtonprof\stp_api;

class stpAbonnement implements \JsonSerializable
{

    protected $ref_eleve, $ref_formule, $ref_statut_abonnement, $ref_abonnement, $date_creation, $prof_referent, $date_maj, $interrompu, $probleme_paiement, $remarque_inscription, $ref_plan;

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

    public function getRef_eleve()
    {
        return $this->ref_eleve;
    }

    public function setRef_eleve($ref_eleve)
    {
        $this->ref_eleve = $ref_eleve;
    }

    public function getRef_formule()
    {
        return $this->ref_formule;
    }

    public function setRef_formule($ref_formule)
    {
        $this->ref_formule = $ref_formule;
    }

    public function getRef_statut_abonnement()
    {
        return $this->ref_statut_abonnement;
    }

    public function setRef_statut_abonnement($ref_statut_abonnement)
    {
        $this->ref_statut_abonnement = $ref_statut_abonnement;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
    }

    public function getDate_creation()
    {
        return $this->date_creation;
    }

    public function setDate_creation($date_creation)
    {
        $this->date_creation = $date_creation;
    }

    public function getProf_referent()
    {
        return $this->prof_referent;
    }

    public function setProf_referent($prof_referent)
    {
        $this->prof_referent = $prof_referent;
    }

    public function getDate_maj()
    {
        return $this->date_maj;
    }

    public function setDate_maj($date_maj)
    {
        $this->date_maj = $date_maj;
    }

    public function getInterrompu()
    {
        return $this->interrompu;
    }

    public function setInterrompu($interrompu)
    {
        $this->interrompu = $interrompu;
    }

    public function getProbleme_paiement()
    {
        return $this->probleme_paiement;
    }

    public function setProbleme_paiement($probleme_paiement)
    {
        $this->probleme_paiement = $probleme_paiement;
    }

    public function getRemarque_inscription()
    {
        return $this->remarque_inscription;
    }

    public function setRemarque_inscription($remarque_inscription)
    {
        $this->remarque_inscription = $remarque_inscription;
    }

    public function getRef_plan()
    {
        return $this->ref_plan;
    }

    public function setRef_plan($ref_plan)
    {
        $this->ref_plan = $ref_plan;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}