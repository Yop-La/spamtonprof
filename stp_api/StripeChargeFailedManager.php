<?php
namespace spamtonprof\stp_api;

class StripeChargeFailedManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StripeChargeFailed $stripeChargeFailed)
    {
        $q = $this->_db->prepare('insert into stripe_charge_failed(evt_id, ref_abo, cus_email, email_prof, sent, invoice_id, invoice_created) values( :evt_id,:ref_abo,:cus_email,:email_prof, false, :invoice_id, :invoice_created)');
        $q->bindValue(':evt_id', $stripeChargeFailed->getEvt_id());
        $q->bindValue(':ref_abo', $stripeChargeFailed->getRef_abo());
        $q->bindValue(':cus_email', $stripeChargeFailed->getCus_email());
        $q->bindValue(':email_prof', $stripeChargeFailed->getEmail_prof());
        $q->bindValue(':invoice_id', $stripeChargeFailed->getInvoice_id());
        $q->bindValue(':invoice_created', $stripeChargeFailed->getInvoice_created());

        $q->execute();

        $stripeChargeFailed->setRef_charge_failed($this->_db->lastInsertId());

        return ($stripeChargeFailed);
    }

    public function updateSent(StripeChargeFailed $stripeChargeFailed)
    {
        $q = $this->_db->prepare("update stripe_charge_failed set sent = :sent where ref_charge_failed = :ref_charge_failed");
        $q->bindValue(":sent", $stripeChargeFailed->getSent(), \PDO::PARAM_BOOL);
        $q->bindValue(":ref_charge_failed", $stripeChargeFailed->getRef_charge_failed());
        $q->execute();

    }

    public function getAll($info = false, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stripe_charge_failed  ");
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'to_send') {

                    $q = $this->_db->prepare("select * from stripe_charge_failed
                        where sent is null or sent is false order by invoice_created desc limit 5");
                }
            }
        }

        $q->execute();

        $charge_failed = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $payout = new \spamtonprof\stp_api\StripeChargeFailed($data);

            if ($constructor) {
                $constructor["objet"] = $payout;
                $this->construct($constructor);
            }

            $charge_failed[] = $payout;
        }
        return ($charge_failed);
    }
}
