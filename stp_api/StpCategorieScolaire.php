<?php
namespace spamtonprof\stp_api;

class StpCategorieScolaire implements \JsonSerializable
{

    protected $ref_cat_scolaire, $name;

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

    public function getRef_cat_scolaire()
    {
        return $this->ref_cat_scolaire;
    }

    public function setRef_cat_scolaire($ref_cat_scolaire)
    {
        $this->ref_cat_scolaire = $ref_cat_scolaire;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}