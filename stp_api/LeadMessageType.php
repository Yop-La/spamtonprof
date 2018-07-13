<?php
namespace spamtonprof\stp_api;

class leadMessageType implements \JsonSerializable
{

    protected $type, $ref_type;

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

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getRef_type()
    {
        return $this->ref_type;
    }

    public function setRef_type($ref_type)
    {
        $this->ref_type = $ref_type;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}