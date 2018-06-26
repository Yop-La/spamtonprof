<?php
namespace spamtonprof\stp_api;

class stpEleve implements \JsonSerializable
{

    protected $ref_compte_famille, $email, $prenom, $ref_classe, $nom, $telephone, $ref_eleve;

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

    public function getRef_compte_famille()
    {
        return $this->ref_compte_famille;
    }

    public function setRef_compte_famille($ref_compte_famille)
    {
        $this->ref_compte_famille = $ref_compte_famille;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    public function getRef_classe()
    {
        return $this->ref_classe;
    }

    public function setRef_classe($ref_classe)
    {
        $this->ref_classe = $ref_classe;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    public function getRef_eleve()
    {
        return $this->ref_eleve;
    }

    public function setRef_eleve($ref_eleve)
    {
        $this->ref_eleve = $ref_eleve;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}