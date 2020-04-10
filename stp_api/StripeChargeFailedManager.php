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
        $q = $this->_db->prepare('insert into stripe_charge_failed(evt_id, ref_abo, cus_email, email_prof) values( :evt_id,:ref_abo,:cus_email,:email_prof)');
        $q->bindValue(':evt_id', $stripeChargeFailed->getEvt_id());
        $q->bindValue(':ref_abo', $stripeChargeFailed->getRef_abo());
        $q->bindValue(':cus_email', $stripeChargeFailed->getCus_email());
        $q->bindValue(':email_prof', $stripeChargeFailed->getEmail_prof());
        $q->execute();
        
        
        $stripeChargeFailed->setRef_charge_failed($this->_db->lastInsertId());
        
        return ($stripeChargeFailed);
    }
}
