<?php
namespace spamtonprof\stp_api;

class StripeChargeManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    const not_referenced_by_stripe_transaction = 'not_referenced_by_stripe_transaction';

    public function deleteAll($info = false)
    {
        $q = false;
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'not_referenced_by_stripe_transaction') {

                    $q = $this->_db->prepare("delete from stripe_charge where ref not in ( select ref_charge from stripe_transaction);");
                }
            }
        }

        $q->execute();
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
        $q = false;
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'ref_stripe') {

                    $ref_stripe = $params['ref_stripe'];

                    $q = $this->_db->prepare("select * from stripe_charge
                where ref_stripe = :ref_stripe");

                    $q->bindValue(':ref_stripe', $ref_stripe);
                }
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
