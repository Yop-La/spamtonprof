<?php
namespace spamtonprof\stp_api;

class StripeTransactionManager
{

    private $_db;

    const by_ref_payout = 'by_ref_payout';

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stripeTransaction $stripeTransaction)
    {
        $q = $this->_db->prepare('insert into stripe_transaction(transaction_id, transaction_amount, ref_payout, test_mode, available_on, type) values(:transaction_id,:transaction_amount,:ref_payout,:test_mode,:available_on,:type)');

        $q->bindValue(':transaction_id', $stripeTransaction->getTransaction_id());
        $q->bindValue(':transaction_amount', $stripeTransaction->getTransaction_amount());
        $q->bindValue(':ref_payout', $stripeTransaction->getRef_payout());
        $q->bindValue(':test_mode', $stripeTransaction->getTest_mode(), \PDO::PARAM_BOOL);
        $q->bindValue(':available_on', $stripeTransaction->getAvailable_on());
        $q->bindValue(':type', $stripeTransaction->getType());

        $q->execute();

        $stripeTransaction->setRef($this->_db->lastInsertId());

        return ($stripeTransaction);
    }

    public function update_ref_charge(\spamtonprof\stp_api\StripeTransaction $transaction)
    {
        $q = $this->_db->prepare("update stripe_transaction set ref_charge = :ref_charge where ref = :ref");
        $q->bindValue(":ref", $transaction->getRef());
        $q->bindValue(":ref_charge", $transaction->getRef_charge());
        $q->execute();
    }

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

                if ($key == 'by_ref_payout') {

                    $q = $this->_db->prepare("delete from stripe_transaction
                        where ref_payout = :ref_payout'");
                    $q->bindValue(':ref_payout', $params['ref_payout']);
                }
            }
        }

        $q->execute();
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

                if ($key == 'transaction_id') {

                    $transaction_id = $params['transaction_id'];

                    $q = $this->_db->prepare("select * from stripe_transaction
                where transaction_id = :transaction_id");

                    $q->bindValue(':transaction_id', $transaction_id);
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        $charge = false;
        if ($data) {
            $charge = new \spamtonprof\stp_api\StripeTransaction($data);
        }

        return ($charge);
    }

    public function getAll($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stripe_transaction");
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'by_ref_payout') {

                    $q = $this->_db->prepare("select * from stripe_transaction
                        where ref_payout = :ref_payout and type != 'payout'");

                    $q->bindValue(':ref_payout', $params['ref_payout']);
                }

                if ($key == 'ref_charge_is_null') {

                    $q = $this->_db->prepare("select * from stripe_transaction
                        where ref_charge is null and type != 'payout' limit 50");
                }
            }
        }

        $q->execute();

        $transactions = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $transaction = new \spamtonprof\stp_api\StripeTransaction($data);

            if ($constructor) {
                $constructor["objet"] = $transaction;
                $this->construct($constructor);
            }

            $transactions[] = $transaction;
        }
        return ($transactions);
    }
    
    public function cast(\spamtonprof\stp_api\StripeTransaction $object)
    {
        return ($object);
    }
    
    public function construct($constructor)
    {
        $transaction = $this->cast($constructor["objet"]);
        
        $constructOrders = $constructor["construct"];
        
        
        foreach ($constructOrders as $constructOrder) {
            
            switch ($constructOrder) {
                
                case "ref_charge":
                    
                    $chargeMg = new \spamtonprof\stp_api\StripeChargeManager();
                    $constructorCharge = false;
                    
                    if (array_key_exists("ref_charge", $constructor)) {
                        $constructorCharge = $constructor["ref_charge"];
                    }
                    
                    $charge = $chargeMg->get(array('key' => 'ref','params' => array('ref' => $transaction->getRef_charge())),$constructorCharge);
                    
                    
                    $transaction->setCharge($charge);
                    
                    break;
            }
        }
    }
    
    
    
    
    
    
}
