<?php
namespace spamtonprof\stp_api;

class PhoneString implements \JsonSerializable
{

    protected $ref_phone_string, $phone_string;

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

    public function getRef_phone_string()
    {
        return $this->ref_phone_string;
    }

    public function setRef_phone_string($ref_phone_string)
    {
        $this->ref_phone_string = $ref_phone_string;
    }

    public function getPhone_string()
    {
        return $this->phone_string;
    }

    public function setPhone_string($phone_string)
    {
        $this->phone_string = $phone_string;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}