<?php
namespace spamtonprof\stp_api;

class StripePayout implements \JsonSerializable
{

    protected $ref, $ref_stripe, $ref_prof, $amount, $date_versement, $test_mode, $created, $transactions_status, $transactions, $invoice_prof_prod, $invoice_prof_test;

    const cant_retrieve_transactions = 'cant_retrieve_transactions', transactions_retrieved = 'transactions_retrieved', not_all_transactions_retrieved = 'not_all_transactions_retrieved';

    /**
     *
     * @return mixed
     */
    public function getInvoice_prof_prod()
    {
        return $this->invoice_prof_prod;
    }

    /**
     *
     * @param mixed $invoice_prof_prod
     */
    public function setInvoice_prof_prod($invoice_prof_prod)
    {
        $this->invoice_prof_prod = $invoice_prof_prod;
    }

    /**
     *
     * @return mixed
     */
    public function getInvoice_prof_test()
    {
        return $this->invoice_prof_test;
    }

    /**
     *
     * @param mixed $invoice_prof_test
     */
    public function setInvoice_prof_test($invoice_prof_test)
    {
        $this->invoice_prof_test = $invoice_prof_test;
    }

    public function setInvoice_prof($invoice_prof, $test_mode)
    {
        if ($test_mode) {
            $this->setInvoice_prof_test($invoice_prof);
        } else {
            $this->setInvoice_prof_prod($invoice_prof);
        }
    }

    public function getInvoice_prof($test_mode)
    {
        if ($test_mode) {
            return ($this->getInvoice_prof_test());
        }

        return ($this->getInvoice_prof_prod());
    }

    /**
     *
     * @return mixed
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     *
     * @param mixed $transactions
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     *
     * @return mixed
     */
    public function getTransactions_status()
    {
        return $this->transactions_status;
    }

    /**
     *
     * @param mixed $transactions_status
     */
    public function setTransactions_status($transactions_status)
    {
        $this->transactions_status = $transactions_status;
    }

    /**
     *
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     *
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     *
     * @return mixed
     */
    public function getTest_mode()
    {
        return $this->test_mode;
    }

    /**
     *
     * @param mixed $test_mode
     */
    public function setTest_mode($test_mode)
    {
        $this->test_mode = $test_mode;
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

    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getDate_versement()
    {
        return $this->date_versement;
    }

    public function setDate_versement($date_versement)
    {
        $this->date_versement = $date_versement;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}