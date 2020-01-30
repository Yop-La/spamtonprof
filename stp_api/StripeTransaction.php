<?php
namespace spamtonprof\stp_api;

class StripeTransaction implements \JsonSerializable
{

    protected $ref, $transaction_id, $transaction_amount, $ref_payout, $test_mode, $available_on, $type, $ref_charge, $charge;

    /**
     *
     * @return mixed
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     *
     * @param mixed $charge
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_charge()
    {
        return $this->ref_charge;
    }

    /**
     *
     * @param mixed $ref_charge
     */
    public function setRef_charge($ref_charge)
    {
        $this->ref_charge = $ref_charge;
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

    public function getRef()
    {
        return $this->ref;
    }

    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    public function getTransaction_id()
    {
        return $this->transaction_id;
    }

    public function setTransaction_id($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    public function getTransaction_amount()
    {
        return $this->transaction_amount;
    }

    public function setTransaction_amount($transaction_amount)
    {
        $this->transaction_amount = $transaction_amount;
    }

    public function getRef_payout()
    {
        return $this->ref_payout;
    }

    public function setRef_payout($ref_payout)
    {
        $this->ref_payout = $ref_payout;
    }

    public function getTest_mode()
    {
        return $this->test_mode;
    }

    public function setTest_mode($test_mode)
    {
        $this->test_mode = $test_mode;
    }

    public function getAvailable_on()
    {
        return $this->available_on;
    }

    public function setAvailable_on($available_on)
    {
        $this->available_on = $available_on;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}