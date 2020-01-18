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
            }
        }

        $q->execute();

        $transactions = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $interrup = new \spamtonprof\stp_api\StripeTransaction($data);

            // if ($constructor) {
            // $constructor["objet"] = $interrup;
            // $this->construct($constructor);
            // }

            $transactions[] = $interrup;
        }
        return ($transactions);
    }
}
