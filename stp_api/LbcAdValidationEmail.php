<?php
namespace spamtonprof\stp_api;

class LbcAdValidationEmail implements \JsonSerializable
{

    protected $ref_message, $gmail_id, $date_reception, $destinataire;

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

    public function getRef_message()
    {
        return $this->ref_message;
    }

    public function setRef_message($ref_message)
    {
        $this->ref_message = $ref_message;
    }

    public function getGmail_id()
    {
        return $this->gmail_id;
    }

    public function setGmail_id($gmail_id)
    {
        $this->gmail_id = $gmail_id;
    }

    public function getDate_reception()
    {
        return $this->date_reception;
    }

    public function setDate_reception($date_reception)
    {
        $this->date_reception = $date_reception;
    }

    public function getDestinataire()
    {
        return $this->destinataire;
    }

    public function setDestinataire($destinataire)
    {
        $this->destinataire = $destinataire;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}