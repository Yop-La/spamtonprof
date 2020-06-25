<?php
namespace spamtonprof\stp_api;

class StpPole implements \JsonSerializable
{

    protected $ref_pole, $name;

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

    public function getRef_pole()
    {
        return $this->ref_pole;
    }

    public function setRef_pole($ref_pole)
    {
        $this->ref_pole = $ref_pole;
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