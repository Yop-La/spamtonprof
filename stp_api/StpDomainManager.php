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
        $q->bindValue(':mx_ok', $stpDomain->getMx_ok(),PDO::PARAM_BOOL);
        $q->bindValue(':in_black_list', $stpDomain->getIn_black_list(),PDO::PARAM_BOOL);
        $q->execute();

        $stpDomain->setRef_domain($this->_db->lastInsertId());

        return ($stpDomain);
    }
}
