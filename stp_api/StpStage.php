<?php
namespace spamtonprof\stp_api;

class StpStage implements \JsonSerializable
{

    protected $ref_proche, $ref_eleve, $ref_formule, $ref_plan, $date_debut, $date_inscription, $remarque_inscription, $ref_prof, $ref_compte, $subs_id, $ref_stage, $test;

    /**
     * @return mixed
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * @param mixed $test
     */
    public function setTest($test)
    {
        $this->test = $test;
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

    
    
    public function getRef_proche()
    {
        return $this->ref_proche;
    }

    public function setRef_proche($ref_proche)
    {
        $this->ref_proche = $ref_proche;
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

    public function getRef_plan()
    {
        return $this->ref_plan;
    }

    public function setRef_plan($ref_plan)
    {
        $this->ref_plan = $ref_plan;
    }

    public function getDate_debut()
    {
        return $this->date_debut;
    }

    public function setDate_debut($date_debut)
    {
        $this->date_debut = $date_debut;
    }

    public function getDate_inscription()
    {
        return $this->date_inscription;
    }

    public function setDate_inscription($date_inscription)
    {
        $this->date_inscription = $date_inscription;
    }

    public function getRemarque_inscription()
    {
        return $this->remarque_inscription;
    }

    public function setRemarque_inscription($remarque_inscription)
    {
        $this->remarque_inscription = $remarque_inscription;
    }

    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    public function getSubs_id()
    {
        return $this->subs_id;
    }

    public function setSubs_id($subs_id)
    {
        $this->subs_id = $subs_id;
    }

    public function getRef_stage()
    {
        return $this->ref_stage;
    }

    public function setRef_stage($ref_stage)
    {
        $this->ref_stage = $ref_stage;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}