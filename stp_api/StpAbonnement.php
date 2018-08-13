<?php
namespace spamtonprof\stp_api;

class stpAbonnement implements \JsonSerializable
{

    const ACTIF = 1 , ESSAI = 2 , TERMINE = 3;
    
    protected $ref_eleve, $ref_formule, $ref_statut_abonnement, $ref_abonnement, $date_creation, $remarque_inscription, $ref_plan, $eleve, $ref_prof, $formule, $prof, $date_attribution_prof, $first_prof_assigned, $ref_proche, $proche, $plan, $ref_compte;

    /**
     *
     * @return mixed
     */
    public function getFirst_prof_assigned()
    {
        return $this->first_prof_assigned;
    }

    /**
     *
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

    /**
     *
     * @return mixed
     */
    public function getRef_proche()
    {
        return $this->ref_proche;
    }

    /**
     *
     * @param mixed $ref_proche
     */
    public function setRef_proche($ref_proche)
    {
        $this->ref_proche = $ref_proche;
    }

    /**
     *
     * @return mixed
     */
    public function getProche()
    {
        return $this->proche;
    }

    /**
     *
     * @param mixed $proche
     */
    public function setProche($proche)
    {
        $this->proche = $proche;
    }

    /**
     *
     * @return mixed
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     *
     * @param mixed $plan
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    /**
     *
     * @param mixed $ref_compte
     */
    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }
}