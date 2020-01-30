<?php
namespace spamtonprof\stp_api;

class StripeInvoiceManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stripeInvoice $stripeInvoice)
    {
        $q = $this->_db->prepare('insert into stripe_invoice(ref_stripe, period_end, period_start, subscription, description, amount_paid, amount_due, customer_email, customer) values(:ref_stripe,:period_end,:period_start,:subscription,:description,:amount_paid,:amount_due,:customer_email,:customer)');

        $q->bindValue(':ref_stripe', $stripeInvoice->getRef_stripe());
        $q->bindValue(':period_end', $stripeInvoice->getPeriod_end());
        $q->bindValue(':period_start', $stripeInvoice->getPeriod_start());
        $q->bindValue(':subscription', $stripeInvoice->getSubscription());
        $q->bindValue(':description', $stripeInvoice->getDescription());
        $q->bindValue(':amount_paid', $stripeInvoice->getAmount_paid());
        $q->bindValue(':amount_due', $stripeInvoice->getAmount_due());
        $q->bindValue(':customer_email', $stripeInvoice->getCustomer_email());
        $q->bindValue(':customer', $stripeInvoice->getCustomer());
        $q->execute();

        $stripeInvoice->setRef($this->_db->lastInsertId());

        return ($stripeInvoice);
    }
}
