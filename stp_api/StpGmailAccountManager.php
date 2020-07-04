<?php
namespace spamtonprof\stp_api;

use PDO;

class StpGmailAccountManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpGmailAccount $StpGmailAccount)
    {
        $q = $this->_db->prepare('insert into stp_gmail_account(ref_gmail_account, email) values( :ref_gmail_account,:email)');
        $q->bindValue(':ref_gmail_account', $StpGmailAccount->getRef_gmail_account());
        $q->bindValue(':email', $StpGmailAccount->getEmail());

        $q->execute();

        $StpGmailAccount->setRef_gmail_account($this->_db->lastInsertId());

        return ($StpGmailAccount);
    }


    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->_db->prepare('SELECT * FROM stp_gmail_account WHERE ref_gmail_account = :ref_gmail_account');

            $q->bindValue(":ref_gmail_account", $info);

            $q->execute();

            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                return new StpGmailAccount($q->fetch(PDO::FETCH_ASSOC));
            }
        } else {
            $q = $this->_db->prepare('SELECT * FROM stp_gmail_account WHERE email like :email');
            $q->execute([
                ':email' => '%' . $info . '%'
            ]);
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                $res = $q->fetch(PDO::FETCH_ASSOC);
                return new StpGmailAccount($res);
            }
        }
    }

    public function updateCredential(StpGmailAccount $StpGmailAccount)

    {
        $q = $this->_db->prepare('UPDATE stp_gmail_account set credential=:credential
            WHERE ref_gmail_account = :ref_gmail_account');

        $q->bindValue(':credential', $StpGmailAccount->getCredential());

        $q->bindValue(':ref_gmail_account', $StpGmailAccount->getRef_gmail_account());

        $q->execute();
    }

    public function updateLastTimestamp(StpGmailAccount $StpGmailAccount)

    {
        $q = $this->_db->prepare('UPDATE stp_gmail_account set last_timestamp=:last_timestamp
            WHERE ref_gmail_account = :ref_gmail_account');

        $q->bindValue(':last_timestamp', $StpGmailAccount->getLast_timestamp());

        $q->bindValue(':ref_gmail_account', $StpGmailAccount->getRef_gmail_account());

        $q->execute();
    }

    public function updateDateUrlSent(StpGmailAccount $StpGmailAccount)

    {
        $q = $this->_db->prepare('UPDATE stp_gmail_account set date_url_sent=:date_url_sent
            WHERE ref_gmail_account = :ref_gmail_account');

        $q->bindValue(':date_url_sent', $StpGmailAccount->getDate_url_sent());

        $q->bindValue(':ref_gmail_account', $StpGmailAccount->getRef_gmail_account());

        $q->execute();
    }

    public function updateHistoryId(StpGmailAccount $StpGmailAccount)

    {
        $q = $this->_db->prepare('UPDATE stp_gmail_account SET last_history_id =:last_history_id
            WHERE ref_gmail_account = :ref_gmail_account');

        $q->bindValue(':last_history_id', $StpGmailAccount->getLast_history_id());

        $q->bindValue(':ref_gmail_account', $StpGmailAccount->getRef_gmail_account());

        $q->execute();
    }
}
