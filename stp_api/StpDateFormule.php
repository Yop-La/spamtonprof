<?php
namespace spamtonprof\stp_api;

class StpDateFormule implements \JsonSerializable
{

    protected $ref_date_formule, $libelle, $date_debut, $ref_formule, $ref_plan;

    /**
     *
     * @return mixed
     */
    public function getRef_plan()
    {
        return $this->ref_plan;
    }

    /**
     *
     * @param mixed $ref_plan
     */
    public function setRef_plan($ref_plan)
    {
        $this->ref_plan = $ref_plan;
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

    public function getRef_date_formule()
    {
        return $this->ref_date_formule;
    }

    public function setRef_date_formule($ref_date_formule)
    {
        $this->ref_date_formule = $ref_date_formule;
    }

    public function getLibelle()
    {
        return $this->libelle;
    }

    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }

    public function getDate_debut()
    {
        return $this->date_debut;
    }

    public function setDate_debut($date_debut)
    {
        $this->date_debut = $date_debut;
    }

    public function getRef_formule()
    {
        return $this->ref_formule;
    }

    public function setRef_formule($ref_formule)
    {
        $this->ref_formule = $ref_formule;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}