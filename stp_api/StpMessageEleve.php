<?php
namespace spamtonprof\stp_api;

class stpMessageEleve implements \JsonSerializable
{

    protected $message, $ref_abonnement, $date_message, $ref_message;

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

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getRef_abonnement()
    {
        return $this->ref_abonnement;
    }

    public function setRef_abonnement($ref_abonnement)
    {
        $this->ref_abonnement = $ref_abonnement;
    }

    public function getDate_message()
    {
        return $this->date_message;
    }

    public function setDate_message($date_message)
    {
        $this->date_message = $date_message;
    }

    public function getRef_message()
    {
        return $this->ref_message;
    }

    public function setRef_message($ref_message)
    {
        $this->ref_message = $ref_message;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}