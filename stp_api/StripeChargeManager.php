<?php
namespace spamtonprof\stp_api;

class StripeChargeManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stripeCharge $stripeCharge)
    {
        $q = $this->_db->prepare('insert into stripe_charge(ref_stripe, amount, created, customer, invoice) values( :ref_stripe,:amount,:created,:customer, :invoice)');

        $q->bindValue(':ref_stripe', $stripeCharge->getRef_stripe());
        $q->bindValue(':amount', $stripeCharge->getAmount());
        $q->bindValue(':created', $stripeCharge->getCreated());
        $q->bindValue(':customer', $stripeCharge->getCustomer());
        $q->bindValue(':invoice', $stripeCharge->getInvoice());
        $q->execute();

        $stripeCharge->setRef($this->_db->lastInsertId());

        return ($stripeCharge);
    }

    public function get($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stripe_charge where ref = :ref");
        $q->bindValue(":ref", $info);
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                // if ($key == 'by_ref_payout') {

                // $q = $this->_db->prepare("select * from stripe_transaction
                // where ref_payout = :ref_payout and type != 'payout'");

                // $q->bindValue(':ref_payout', $params['ref_payout']);
                // }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        $charge = false;
        if ($data) {
            $charge = new \spamtonprof\stp_api\StripeCharge($data);
        }

        return ($charge);
    }
}
