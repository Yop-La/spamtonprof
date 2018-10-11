<?php
namespace spamtonprof\stp_api;

class LbcCommune implements \JsonSerializable
{

    protected $code_insee, $nom_commune, $code_postal, $libelle, $nom_reg, $nom_dep, $code_reg, $code_com, $code_dep, $population, $nom_com, $ref_commune;

    /**
     * @return mixed
     */
    public function getRef_commune()
    {
        return $this->ref_commune;
    }

    /**
     * @param mixed $ref_commune
     */
    public function setRef_commune($ref_commune)
    {
        $this->ref_commune = $ref_commune;
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

    public function getCode_insee()
    {
        return $this->code_insee;
    }

    public function setCode_insee($code_insee)
    {
        $this->code_insee = $code_insee;
    }

    public function getNom_commune()
    {
        return $this->nom_commune;
    }

    public function setNom_commune($nom_commune)
    {
        $this->nom_commune = $nom_commune;
    }

    public function getCode_postal()
    {
        return $this->code_postal;
    }

    public function setCode_postal($code_postal)
    {
        $this->code_postal = $code_postal;
    }

    public function getLibelle()
    {
        return $this->libelle;
    }

    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }

    public function getNom_reg()
    {
        return $this->nom_reg;
    }

    public function setNom_reg($nom_reg)
    {
        $this->nom_reg = $nom_reg;
    }

    public function getNom_dep()
    {
        return $this->nom_dep;
    }

    public function setNom_dep($nom_dep)
    {
        $this->nom_dep = $nom_dep;
    }

    public function getCode_reg()
    {
        return $this->code_reg;
    }

    public function setCode_reg($code_reg)
    {
        $this->code_reg = $code_reg;
    }

    public function getCode_com()
    {
        return $this->code_com;
    }

    public function setCode_com($code_com)
    {
        $this->code_com = $code_com;
    }

    public function getCode_dep()
    {
        return $this->code_dep;
    }

    public function setCode_dep($code_dep)
    {
        $this->code_dep = $code_dep;
    }

    public function getPopulation()
    {
        return $this->population;
    }

    public function setPopulation($population)
    {
        $this->population = $population;
    }

    public function getNom_com()
    {
        return $this->nom_com;
    }

    public function setNom_com($nom_com)
    {
        $this->nom_com = $nom_com;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}