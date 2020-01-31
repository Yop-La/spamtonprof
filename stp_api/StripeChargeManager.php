<?php
namespace spamtonprof\stp_api;

class StripeChargeManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function update_updated(\spamtonprof\stp_api\StripeCharge $charge)
    {
        $q = $this->_db->prepare("update stripe_charge set updated = :updated where ref = :ref");
        $q->bindValue(":ref", $charge->getRef());
        $q->bindValue(":updated", $charge->getUpdated(), \PDO::PARAM_BOOL);
        $q->execute();
    }

    public function update_nom_formule(\spamtonprof\stp_api\StripeCharge $charge)
    {
        $q = $this->_db->prepare("update stripe_charge set nom_formule = :nom_formule where ref = :ref");
        $q->bindValue(":ref", $charge->getRef());
        $q->bindValue(":nom_formule", $charge->getNom_formule());
        $q->execute();
    }

    public function update_ref_invoice(\spamtonprof\stp_api\StripeCharge $charge)
    {
        $q = $this->_db->prepare("update stripe_charge set ref_invoice = :ref_invoice where ref = :ref");
        $q->bindValue(":ref", $charge->getRef());
        $q->bindValue(":ref_invoice", $charge->getRef_invoice());
        $q->execute();
    }

    public function update_ref_abo(\spamtonprof\stp_api\StripeCharge $charge)
    {
        $q = $this->_db->prepare("update stripe_charge set ref_abo = :ref_abo where ref = :ref");
        $q->bindValue(":ref", $charge->getRef());
        $q->bindValue(":ref_abo", $charge->getRef_abo());
        $q->execute();
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

                if ($key == 'ref_abo_is_null') {

                    $q = $this->_db->prepare("select * from stripe_charge
                        where ref_abo is null limit 70");
                }

                if ($key == 'ref_invoice_is_null') {

                    $q = $this->_db->prepare("select * from stripe_charge
                        where ref_invoice is null limit 150");
                }

                if ($key == 'updated_is_null') {

                    $q = $this->_db->prepare('select * from stripe_charge
                        where updated is null order by "ref" limit 150 ');
                }
            }
        }

        $q->execute();

        $transactions = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $interrup = new \spamtonprof\stp_api\StripeCharge($data);

            // if ($constructor) {
            // $constructor["objet"] = $interrup;
            // $this->construct($constructor);
            // }

            $transactions[] = $interrup;
        }
        return ($transactions);
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

                if ($key == 'ref') {

                    $ref = $params['ref'];

                    $q = $this->_db->prepare("select * from stripe_charge
                where ref = :ref");

                    $q->bindValue(':ref', $ref);
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        $charge = false;
        if ($data) {
            $charge = new \spamtonprof\stp_api\StripeCharge($data);

            if ($constructor) {
                $constructor["objet"] = $charge;
                $this->construct($constructor);
            }
        }

        return ($charge);
    }

    public function cast(\spamtonprof\stp_api\StripeCharge $object)
    {
        return ($object);
    }

    public function construct($constructor)
    {
        $charge = $this->cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {

                case "ref_abo":

                    $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
                    $constructorAbo = false;

                    if (array_key_exists("ref_abo", $constructor)) {
                        $constructorAbo = $constructor["ref_abo"];
                    }

                    $abo = $aboMg->get(array(
                        'ref_abonnement' => $charge->getRef_abo()
                    ), $constructorAbo);
                    $charge->setAbo($abo);

                    break;
                case "ref_invoice":

                    $invoiceMg = new \spamtonprof\stp_api\StripeInvoiceManager();
                    $constructorInvoice = false;

                    if (array_key_exists("ref_invoice", $constructor)) {
                        $constructorAbo = $constructor["ref_invoice"];
                    }

                    $invoice = $invoiceMg->get(array(
                        'key' => 'ref',
                        'params' => array(
                            'ref' => $charge->getRef_invoice()
                        )
                    ), $constructorInvoice);
                    $charge->setInvoice($invoice);

                    break;
            }
        }
    }
}
