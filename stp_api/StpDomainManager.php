<?php
namespace spamtonprof\stp_api;

use PDO;

class StpDomainManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpDomain $stpDomain)
    {
        $q = $this->_db->prepare('insert into stp_domain(name, mail_provider, mx_ok, in_black_list) values( :name,:mail_provider,:mx_ok,:in_black_list)');
        $q->bindValue(':name', $stpDomain->getName());
        $q->bindValue(':mail_provider', $stpDomain->getMail_provider());
        $q->bindValue(':mx_ok', $stpDomain->getMx_ok(), PDO::PARAM_BOOL);
        $q->bindValue(':in_black_list', $stpDomain->getIn_black_list(), PDO::PARAM_BOOL);
        $q->execute();

        $stpDomain->setRef_domain($this->_db->lastInsertId());

        return ($stpDomain);
    }

    public function getAll($info)
    {
        $domains = [];

        if (array_key_exists("mail_provider", $info)) {
            $mail_provider = $info["mail_provider"];
            $q = $this->_db->prepare("select * from stp_domain where mail_provider = :mail_provider");
            $q->bindValue(":mail_provider", $mail_provider);
            $q->execute();

            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

                $domains[] = new \spamtonprof\stp_api\StpDomain($data);
            }

            return ($domains);
        }

        if (! $data) {
            return false;
        }

        return (new \spamtonprof\stp_api\MailForLead($data));
    }
}
