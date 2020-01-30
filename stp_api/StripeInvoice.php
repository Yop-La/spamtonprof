<?php
namespace spamtonprof\stp_api;

class StripeInvoice implements \JsonSerializable
{

    protected $ref, $ref_stripe, $period_end, $period_start, $subscription, $description, $amount_paid, $amount_due, $customer_email, $customer;

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

    public function getRef_stripe()
    {
        return $this->ref_stripe;
    }

    public function setRef_stripe($ref_stripe)
    {
        $this->ref_stripe = $ref_stripe;
    }

    public function getPeriod_end()
    {
        return $this->period_end;
    }

    public function setPeriod_end($period_end)
    {
        $this->period_end = $period_end;
    }

    public function getPeriod_start()
    {
        return $this->period_start;
    }

    public function setPeriod_start($period_start)
    {
        $this->period_start = $period_start;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }

    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getAmount_paid()
    {
        return $this->amount_paid;
    }

    public function setAmount_paid($amount_paid)
    {
        $this->amount_paid = $amount_paid;
    }

    public function getAmount_due()
    {
        return $this->amount_due;
    }

    public function setAmount_due($amount_due)
    {
        $this->amount_due = $amount_due;
    }

    public function getCustomer_email()
    {
        return $this->customer_email;
    }

    public function setCustomer_email($customer_email)
    {
        $this->customer_email = $customer_email;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}