<?php
namespace spamtonprof\stp_api;

class StripeCharge implements \JsonSerializable
{

    protected $ref, $ref_stripe, $amount, $created, $customer, $invoice, $ref_abo, $nom_formule, $updated, $abo, $ref_invoice, $full_invoice;

    /**
     *
     * @return mixed
     */
    public function getFull_invoice()
    {
        return $this->full_invoice;
    }

    /**
     *
     * @param mixed $full_invoice
     */
    public function setFull_invoice($full_invoice)
    {
        $this->full_invoice = $full_invoice;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_invoice()
    {
        return $this->ref_invoice;
    }

    /**
     *
     * @param mixed $ref_invoice
     */
    public function setRef_invoice($ref_invoice)
    {
        $this->ref_invoice = $ref_invoice;
    }

    /**
     *
     * @return mixed
     */
    public function getAbo()
    {
        return $this->abo;
    }

    /**
     *
     * @param mixed $abo
     */
    public function setAbo($abo)
    {
        $this->abo = $abo;
    }

    /**
     *
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     *
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     *
     * @return mixed
     */
    public function getNom_formule()
    {
        return $this->nom_formule;
    }

    /**
     *
     * @param mixed $nom_formule
     */
    public function setNom_formule($nom_formule)
    {
        $this->nom_formule = $nom_formule;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_abo()
    {
        return $this->ref_abo;
    }

    /**
     *
     * @param mixed $ref_abo
     */
    public function setRef_abo($ref_abo)
    {
        $this->ref_abo = $ref_abo;
    }

    /**
     *
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     *
     * @param mixed $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     *
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     *
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
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

    public function getRef_stripe()
    {
        return $this->ref_stripe;
    }

    public function setRef_stripe($ref_stripe)
    {
        $this->ref_stripe = $ref_stripe;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}