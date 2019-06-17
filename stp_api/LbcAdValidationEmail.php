<?php
namespace spamtonprof\stp_api;

class LbcAdValidationEmail implements \JsonSerializable
{

    protected $ref_message, $gmail_id, $date_reception, $ref_compte_lbc;

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

    /**
     *
     * @return mixed
     */
    public function getRef_compte_lbc()
    {
        return $this->ref_compte_lbc;
    }

    /**
     *
     * @param mixed $ref_compte_lbc
     */
    public function setRef_compte_lbc($ref_compte_lbc)
    {
        $this->ref_compte_lbc = $ref_compte_lbc;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}