<?php
namespace spamtonprof\stp_api;

class StpMessageEleveManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpMessageEleve $StpMessageEleve)
    {
        $q = $this->_db->prepare('insert into stp_message_eleve(ref_abonnement, date_message, ref_gmail, mail_expe) values(:ref_abonnement, :date_message, :ref_gmail, :mail_expe)');
        $q->bindValue(':ref_abonnement', $StpMessageEleve->getRef_abonnement());
        $q->bindValue(':ref_gmail', $StpMessageEleve->getRef_gmail());
        $q->bindValue(':date_message', $StpMessageEleve->getDate_message());
        $q->bindValue(':mail_expe', $StpMessageEleve->getMail_expe());
        $q->execute();

        $StpMessageEleve->setRef_message($this->_db->lastInsertId());

        return ($StpMessageEleve);
    }

    public function deleteAll($info)
    {
        $q = $this->_db->prepare("delete from stp_message_eleve where ref_abonnement =:ref_abonnement");
        $q->bindValue(":ref_abonnement", $info);
        $q->execute();
    }

    public function getAll($info)
    {
        $msgs = [];

        if (is_array($info)) {

            if (array_key_exists('week', $info) && array_key_exists('ref_prof', $info) && array_key_exists('mail_expe', $info)) {

                $week = $info['week'];
                $ref_prof = $info['ref_prof'];
                $mail_expe = $info['mail_expe'];

                $q = $this->_db->prepare("
                    select stp_message_eleve.ref_abonnement, date_message, ref_gmail,mail_expe, ref_message from stp_message_eleve, stp_abonnement 
                        where  extract(week from date_message) = :week
                            and stp_message_eleve.ref_abonnement = stp_abonnement.ref_abonnement
                            and lower(mail_expe) like lower(:mail_expe)
                            and stp_abonnement.ref_prof = :ref_prof");
                $q->bindValue(':week', $week);
                $q->bindValue((':mail_expe'), $mail_expe);
                $q->bindValue(':ref_prof', $ref_prof);
                $q->execute();
            }
        }

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $msg = new \spamtonprof\stp_api\StpMessageEleve($data);
            $msgs[] = $msg;
        }

        return ($msgs);
    }
}
