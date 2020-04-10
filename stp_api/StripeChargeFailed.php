<?php
namespace spamtonprof\stp_api;

class StripeChargeFailed implements \JsonSerializable
{

    protected $evt_id, $ref_abo, $cus_email, $email_prof, $ref_charge_failed;

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

    public function getEvt_id()
    {
        return $this->evt_id;
    }

    public function setEvt_id($evt_id)
    {
        $this->evt_id = $evt_id;
    }

    public function getRef_abo()
    {
        return $this->ref_abo;
    }

    public function setRef_abo($ref_abo)
    {
        $this->ref_abo = $ref_abo;
    }

    public function getCus_email()
    {
        return $this->cus_email;
    }

    public function setCus_email($cus_email)
    {
        $this->cus_email = $cus_email;
    }

    public function getEmail_prof()
    {
        return $this->email_prof;
    }

    public function setEmail_prof($email_prof)
    {
        $this->email_prof = $email_prof;
    }

    public function getRef_charge_failed()
    {
        return $this->ref_charge_failed;
    }

    public function setRef_charge_failed($ref_charge_failed)
    {
        $this->ref_charge_failed = $ref_charge_failed;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}