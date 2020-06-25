<?php
namespace spamtonprof\stp_api;

class StpLeadSpamExpressManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpLeadSpamExpress $stpLeadSpamExpress)
    {
        $q = $this->_db->prepare('insert into stp_lead_spam_express(name, email) values(:name,:email)');
        $q->bindValue(':name', $stpLeadSpamExpress->getName());
        $q->bindValue(':email', $stpLeadSpamExpress->getEmail());
        $q->execute();

        $stpLeadSpamExpress->setRef_lead($this->_db->lastInsertId());

        return ($stpLeadSpamExpress);
    }

    public function update_name(\spamtonprof\stp_api\StpLeadSpamExpress $lead)
    {
        $q = $this->_db->prepare("update stp_lead_spam_express set name = :name where ref_lead = :ref_lead");
        $q->bindValue(":name", $lead->getName());
        $q->bindValue(":ref_lead", $lead->getRef_lead());
        $q->execute();
    }

    public function update_email(\spamtonprof\stp_api\StpLeadSpamExpress $lead)
    {
        $q = $this->_db->prepare("update stp_lead_spam_express set email = :email where ref_lead = :ref_lead");
        $q->bindValue(":email", $lead->getEmail());
        $q->bindValue(":ref_lead", $lead->getRef_lead());
        $q->execute();
    }

    public function getAll($info = false, $constructor = false)
    {
        $leads = [];

        $q = $this->_db->prepare("select * from lead_spam_express");

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];
            }
        }

        $q->execute();

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $lead = new \spamtonprof\stp_api\StpLeadSpamExpress($data);

            if ($constructor) {
                $constructor["objet"] = $lead;
                $this->construct($constructor);
            }
            $leads[] = $lead;
        }
        return ($leads);
    }

    public function get($info, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stp_lead_spam_express where ref_lead = :ref_lead");
        $q->bindValue('ref_lead', $info);

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'get_by_email') {
                    $email = $params['email'];
                    $q = $this->_db->prepare("select * from stp_lead_spam_express where email like lower(:email)");
                    $q->bindValue('email', $email);
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $lead = new \spamtonprof\stp_api\StpLeadSpamExpress($data);
        }

        if ($constructor) {
            $constructor["objet"] = $lead;
            $this->construct($constructor);
        }

        return ($lead);
    }
}
