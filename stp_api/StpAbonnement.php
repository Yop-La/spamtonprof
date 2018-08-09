<?php
namespace spamtonprof\stp_api;

class stpAbonnement implements \JsonSerializable
{

    protected $ref_eleve, $ref_formule, $ref_statut_abonnement, $ref_abonnement, $date_creation, $remarque_inscription, $ref_plan, $eleve, $ref_prof, $formule, $prof, $date_attribution_prof, $first_prof_assigned;

    /**
     * @return mixed
     */
    public function getFirst_prof_assigned()
    {
        return $this->first_prof_assigned;
    }

    /**
     * @param mixed $first_prof_assigned
     */
    public function setFirst_prof_assigned($first_prof_assigned)
    {
        $this->first_prof_assigned = $first_prof_assigned;
    }

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

    /**
     *
     * @return mixed
     */
    public function getProf()
    {
        return $this->prof;
    }

    /**
     *
     * @param mixed $prof
     */
    public function setProf($prof)
    {
        $this->prof = $prof;
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

    /**
     *
     * @return mixed
     */
    public function getEleve()
    {
        return $this->eleve;
    }

    /**
     *
     * @param mixed $eleve
     */
    public function setEleve($eleve)
    {
        if (is_array($eleve)) {}
        
        $this->eleve = $eleve;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    /**
     *
     * @param mixed $ref_prof
     */
    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    /**
     *
     * @return mixed
     */
    public function getFormule()
    {
        return $this->formule;
    }

    /**
     *
     * @param mixed $formule
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_attribution_prof()
    {
        return $this->date_attribution_prof;
    }

    /**
     *
     * @param mixed $date_attribution_prof
     */
    public function setDate_attribution_prof($date_attribution_prof)
    {
        $this->date_attribution_prof = $date_attribution_prof;
    }
}