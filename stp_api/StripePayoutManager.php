<?php
namespace spamtonprof\stp_api;

class StripePayoutManager
{

    const teacher_and_month = 'teacher_and_month';

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function update_transactions_status(\spamtonprof\stp_api\StripePayout $payout)
    {
        $q = $this->_db->prepare("update stripe_payout set transactions_status = :transactions_status where ref = :ref");
        $q->bindValue(":ref", $payout->getRef());
        $q->bindValue(":transactions_status", $payout->getTransactions_status());
        $q->execute();
    }

    public function add(stripePayout $stripePayout)
    {
        $q = $this->_db->prepare('insert into stripe_payout( ref_stripe, ref_prof, amount, date_versement, test_mode, created) values(:ref_stripe,:ref_prof,:amount,:date_versement, :test_mode, :created)');

        $q->bindValue(':ref_stripe', $stripePayout->getRef_stripe());
        $q->bindValue(':ref_prof', $stripePayout->getRef_prof());
        $q->bindValue(':amount', $stripePayout->getAmount());
        $q->bindValue(':date_versement', $stripePayout->getDate_versement());
        $q->bindValue(':created', $stripePayout->getCreated());
        $q->bindValue(':test_mode', $stripePayout->getTest_mode(), \PDO::PARAM_BOOL);
        $q->execute();

        // if ($q->errorCode() != "00000") {
        // prettyPrint($q->errorInfo());
        // }

        $stripePayout->setRef($this->_db->lastInsertId());

        return ($stripePayout);
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

                if ($key == 'last_payout_of_prof') {

                    $ref_prof = $params['ref_prof'];
                    $q = $this->_db->prepare("select * from stripe_payout where ref_prof = :ref_prof order by created desc limit 1");
                    $q->bindValue('ref_prof', $ref_prof);
                }

                if ($key == 'first_payout_of_prof') {

                    $ref_prof = $params['ref_prof'];
                    $q = $this->_db->prepare("select * from stripe_payout where ref_prof = :ref_prof order by created limit 1");
                    $q->bindValue('ref_prof', $ref_prof);
                }

                if ($key == 'ref') {

                    $ref = $params['ref'];
                    $q = $this->_db->prepare("select * from stripe_payout where ref = :ref");
                    $q->bindValue('ref', $ref);
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        $payout = false;
        if ($data) {
            $payout = new \spamtonprof\stp_api\StripePayout($data);

            if ($constructor) {
                $constructor["objet"] = $payout;
                $this->construct($constructor);
            }
        }

        return ($payout);
    }

    public function getAll($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stripe_payout ");
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'teacher_and_month_and_year') {

                    $q = $this->_db->prepare("select * from stripe_payout 
                        where extract(month from date_versement) = :month 
                            and extract(year from date_versement) = :year
                            and ref_prof = :ref_prof");

                    $q->bindValue(':month', $params['month']);
                    $q->bindValue(':year', $params['year']);
                    $q->bindValue(':ref_prof', $params['ref_prof']);
                }

                if ($key == 'no_transactions_status') {
                    $q = $this->_db->prepare("select * from stripe_payout where (transactions_status is null or transactions_status like '" . \spamtonprof\stp_api\StripePayout::not_all_transactions_retrieved . "') order by ref desc limit 20");
                }
            }
        }

        $q->execute();

        $payouts = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $payout = new \spamtonprof\stp_api\StripePayout($data);

            if ($constructor) {
                $constructor["objet"] = $payout;
                $this->construct($constructor);
            }

            $payouts[] = $payout;
        }
        return ($payouts);
    }

    public function cast(\spamtonprof\stp_api\StripePayout $object)
    {
        return ($object);
    }

    public function construct($constructor)
    {
        $payout = $this->cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {

                case "transactions":

                    $transactionMg = new \spamtonprof\stp_api\StripeTransactionManager();
                    $constructorTransaction = false;

                    if (array_key_exists("transactions", $constructor)) {
                        $constructorTransaction = $constructor["transactions"];
                    }
                    

                    $transactions = $transactionMg->getAll(array(
                        'key' => 'by_ref_payout',
                        'params' => array(
                            "ref_payout" => $payout->getRef()
                        )
                    ), $constructorTransaction);

                    $payout->setTransactions($transactions);
                    break;
            }
        }
    }
}
