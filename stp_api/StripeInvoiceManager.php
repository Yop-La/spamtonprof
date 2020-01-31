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

                if ($key == 'ref') {

                    $ref = $params['ref'];

                    $q = $this->_db->prepare("select * from stripe_invoice
                where ref = :ref");
                    $q->bindValue(':ref', $ref);
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        $invoice = false;
        if ($data) {
            $invoice = new \spamtonprof\stp_api\StripeInvoice($data);

            if ($constructor) {
                $constructor["objet"] = $invoice;
                $this->construct($constructor);
            }
        }

        return ($invoice);
    }

    public function cast(\spamtonprof\stp_api\StripeInvoice $object)
    {
        return ($object);
    }
}
