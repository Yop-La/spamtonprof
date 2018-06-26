<?php
namespace spamtonprof\stp_api;

class stpRemarqueInscription implements \JsonSerializable
{

    protected $ref_abonnement, $chapitre, $difficulte, $note, $ref_matiere, $ref_remarque;

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

    public function getChapitre()
    {
        return $this->chapitre;
    }

    public function setChapitre($chapitre)
    {
        $this->chapitre = $chapitre;
    }

    public function getDifficulte()
    {
        return $this->difficulte;
    }

    public function setDifficulte($difficulte)
    {
        $this->difficulte = $difficulte;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function getRef_matiere()
    {
        return $this->ref_matiere;
    }

    public function setRef_matiere($ref_matiere)
    {
        $this->ref_matiere = $ref_matiere;
    }

    public function getRef_remarque()
    {
        return $this->ref_remarque;
    }

    public function setRef_remarque($ref_remarque)
    {
        $this->ref_remarque = $ref_remarque;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}