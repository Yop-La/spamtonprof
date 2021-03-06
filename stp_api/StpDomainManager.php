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
        $q = $this->_db->prepare('insert into stp_domain(name, mail_provider, mx_ok, in_black_list,disabled) values( :name,:mail_provider,:mx_ok,:in_black_list,false)');
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

        if (array_key_exists("mail_provider", $info) && ! array_key_exists("disabled", $info)) {
            $mail_provider = $info["mail_provider"];
            $q = $this->_db->prepare("select * from stp_domain where mail_provider = :mail_provider");
            $q->bindValue(":mail_provider", $mail_provider);
            $q->execute();

            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

                $domains[] = new \spamtonprof\stp_api\StpDomain($data);
            }

            return ($domains);
        } else if (array_key_exists("mx_ok", $info) && ! array_key_exists("in_black_list", $info) && ! array_key_exists("disabled", $info)) {
            $mx_ok = $info["mx_ok"];
            $q = $this->_db->prepare("select * from stp_domain where mx_ok = :mx_ok");
            $q->bindValue(":mx_ok", $mx_ok, PDO::PARAM_BOOL);
            $q->execute();

            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

                $domains[] = new \spamtonprof\stp_api\StpDomain($data);
            }

            return ($domains);
        } else if (array_key_exists("in_black_list", $info) && ! array_key_exists("mx_ok", $info) && ! array_key_exists("disabled", $info)) {
            $in_black_list = $info["in_black_list"];
            $q = $this->_db->prepare("select * from stp_domain where in_black_list = :in_black_list");
            $q->bindValue(":in_black_list", $in_black_list, PDO::PARAM_BOOL);
            $q->execute();

            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

                $domains[] = new \spamtonprof\stp_api\StpDomain($data);
            }

            return ($domains);
        } else if (array_key_exists("in_black_list", $info) && array_key_exists("mx_ok", $info) && ! array_key_exists("disabled", $info)) {
            $in_black_list = $info["in_black_list"];
            $mx_ok = $info["mx_ok"];
            $q = $this->_db->prepare("select * from stp_domain where in_black_list = :in_black_list and mx_ok = :mx_ok");
            $q->bindValue(":in_black_list", $in_black_list, PDO::PARAM_BOOL);
            $q->bindValue(":mx_ok", $mx_ok, PDO::PARAM_BOOL);
            $q->execute();

            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

                $domains[] = new \spamtonprof\stp_api\StpDomain($data);
            }

            return ($domains);
        } else if (array_key_exists("in_black_list", $info) && array_key_exists("mx_ok", $info) && array_key_exists("disabled", $info)) {
            $in_black_list = $info["in_black_list"];
            $mx_ok = $info["mx_ok"];
            $disabled = $info["disabled"];
            $q = $this->_db->prepare("select * from stp_domain where in_black_list = :in_black_list and mx_ok = :mx_ok and disabled = :disabled");
            $q->bindValue(":in_black_list", $in_black_list, PDO::PARAM_BOOL);
            $q->bindValue(":mx_ok", $mx_ok, PDO::PARAM_BOOL);
            $q->bindValue(":disabled", $disabled, PDO::PARAM_BOOL);
            $q->execute();

            while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

                $domains[] = new \spamtonprof\stp_api\StpDomain($data);
            }

            return ($domains);
        } else if (in_array("domains_to_validate", $info)) {

            $q = $this->_db->prepare("select * from stp_domain where mx_ok is true and ( ready is null or ready is false )");

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

    public function getStatsOnValidDomain()
    {
        $this->_db->exec("create or replace view valid_domain as(
            select * from stp_domain where mail_provider like 'mailgun' and in_black_list is false and ready is true and disabled is false
            );
            
            create or replace view stat_domain as(
                select split_part(mail, '@', 2) as domain, count(split_part(mail, '@', 2)) as nb_use  from compte_lbc group by split_part(mail, '@', 2) order by nb_use desc
            );
            
            create or replace view stat_valid_domain as(
                SELECT name, ref_domain,mail_provider,in_black_list,mx_ok,ready, disabled, coalesce(nb_use, 0) as nb_use
                FROM valid_domain
                LEFT OUTER JOIN stat_domain
                ON valid_domain.name  = stat_domain.domain
            
            );");

        $q = $this->_db->prepare("

            select * from stat_valid_domain order by nb_use desc;
        ");

        $q->execute();

        $domains = [];
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $domains[] = new \spamtonprof\stp_api\StpDomain($data);
        }

        return ($domains);
    }

    public function updateMxOk(\spamtonprof\stp_api\StpDomain $domain)
    {
        $q = $this->_db->prepare("update stp_domain set mx_ok = :mx_ok where ref_domain = :ref_domain");
        $q->bindValue(":ref_domain", $domain->getRef_domain());
        $q->bindValue(":mx_ok", $domain->getMx_ok(), PDO::PARAM_BOOL);
        $q->execute();
    }

    public function updateReady(\spamtonprof\stp_api\StpDomain $domain)
    {
        $q = $this->_db->prepare("update stp_domain set ready = :ready where ref_domain = :ref_domain");
        $q->bindValue(":ref_domain", $domain->getRef_domain());
        $q->bindValue(":ready", $domain->getReady(), PDO::PARAM_BOOL);
        $q->execute();
    }

    public function updateDisabled(\spamtonprof\stp_api\StpDomain $domain)
    {
        $q = $this->_db->prepare("update stp_domain set disabled = :disabled where ref_domain = :ref_domain");
        $q->bindValue(":ref_domain", $domain->getRef_domain());
        $q->bindValue(":disabled", $domain->getDisabled(), PDO::PARAM_BOOL);
        $q->execute();
    }

    public function get($info)
    {
        if (array_key_exists("name", $info)) {
            $name = $info["name"];
            $q = $this->_db->prepare("select * from stp_domain where name = :name");
            $q->bindValue(":name", $name);
            $q->execute();
        }

        $data = $q->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $domain = new \spamtonprof\stp_api\StpDomain($data);
            return ($domain);
        } else {
            return (false);
        }
    }
}
