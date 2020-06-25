<?php
namespace spamtonprof\stp_api;

class StpCmdSpamExpressManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpCmdSpamExpress $stpCmdSpamExpress)
    {
        $q = $this->_db->prepare('insert into stp_cmd_spam_express(ref_lead, ref_cat_scolaire, status, ref_pole) values(:ref_lead,:ref_cat_scolaire,:status,:ref_pole)');

        $q->bindValue(':ref_lead', $stpCmdSpamExpress->getRef_lead());
        $q->bindValue(':ref_cat_scolaire', $stpCmdSpamExpress->getRef_cat_scolaire());
        $q->bindValue(':status', $stpCmdSpamExpress->getStatus());
        $q->bindValue(':ref_pole', $stpCmdSpamExpress->getRef_pole());
        $q->execute();

        $stpCmdSpamExpress->setRef_cmd($this->_db->lastInsertId());

        return ($stpCmdSpamExpress);
    }

    public function get($info, $constructor = false)
    {
        $q = $this->_db->prepare("select * from stp_cmd_spam_express where ref_cmd = :ref_cmd");
        $q->bindValue('ref_cmd', $info);

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }
            }
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $cmd = new \spamtonprof\stp_api\StpCmdSpamExpress($data);
        }

        if ($constructor) {
            $constructor["objet"] = $cmd;
            $this->construct($constructor);
        }

        return ($cmd);
    }

    public static function cast(\spamtonprof\stp_api\StpCmdSpamExpress $object)
    {
        return ($object);
    }

    public function update_ref_cat_scolaire(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set ref_cat_scolaire = :ref_cat_scolaire where ref_cmd = :ref_cmd");
        $q->bindValue(":ref_cat_scolaire", $cmd->getRef_cat_scolaire());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function update_ref_prof(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set ref_prof = :ref_prof where ref_cmd = :ref_cmd");
        $q->bindValue(":ref_prof", $cmd->getRef_prof());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function update_transfert_id(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set transfert_id = :transfert_id where ref_cmd = :ref_cmd");
        $q->bindValue(":transfert_id", $cmd->getTransfert_id());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function update_payment_intent_id(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set payment_intent_id = :payment_intent_id where ref_cmd = :ref_cmd");
        $q->bindValue(":payment_intent_id", $cmd->getPayment_intent_id());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function update_ref_pole(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set ref_pole = :ref_pole where ref_cmd = :ref_cmd");
        $q->bindValue(":ref_pole", $cmd->getRef_pole());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function update_ref_offre(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set ref_offre = :ref_offre where ref_cmd = :ref_cmd");
        $q->bindValue(":ref_offre", $cmd->getRef_offre());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function update_status(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set status = :status where ref_cmd = :ref_cmd");
        $q->bindValue(":status", $cmd->getStatus());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function update_remarque(\spamtonprof\stp_api\StpCmdSpamExpress $cmd)
    {
        $q = $this->_db->prepare("update stp_cmd_spam_express set remarque = :remarque where ref_cmd = :ref_cmd");
        $q->bindValue(":remarque", $cmd->getRemarque());
        $q->bindValue(":ref_cmd", $cmd->getRef_cmd());
        $q->execute();
    }

    public function construct($constructor)
    {
        $cmd = self::cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {
                case "ref_lead":
                    $stp_lead_mg = new \spamtonprof\stp_api\StpLeadSpamExpressManager();

                    $lead = $stp_lead_mg->get($cmd->getRef_lead());
                    $cmd->setLead($lead);

                    break;
                case "offres":
                    $stp_offre_mg = new \spamtonprof\stp_api\StpOffreSpamExpressManager();
                    $offres = $stp_offre_mg->getAll(array(
                        'key' => 'get_by_cat_and_pole',
                        'params' => array(
                            'ref_cat' => $cmd->getRef_cat_scolaire(),
                            'ref_pole' => $cmd->getRef_pole()
                        )
                    ));
                    $cmd->setOffres($offres);
                    break;
                case "ref_pole":
                    $pole_mg = new \spamtonprof\stp_api\StpPoleManager();
                    $pole = $pole_mg->get($cmd->getRef_pole());
                    $cmd->setPole($pole);
                    break;
                case "ref_offre":
                    $offre_mg = new \spamtonprof\stp_api\StpOffreSpamExpressManager();
                    $offre = $offre_mg->get($cmd->getRef_offre());
                    $cmd->setOffre($offre);
                    break;
                case "ref_prof":
                    $prof_mg = new \spamtonprof\stp_api\StpProfManager();
                    $prof = $prof_mg->get(array(
                        'ref_prof' => $cmd->getRef_prof()
                    ));
                    $cmd->setProf($prof);
                    break;
                case "specified_offers":
                    $stp_offre_mg = new \spamtonprof\stp_api\StpOffreSpamExpressManager();
                    $offres = $stp_offre_mg->getAll(array(
                        'key' => 'get_by_cat_and_pole_and_ref_offer',
                        'params' => array(
                            'ref_cat' => $cmd->getRef_cat_scolaire(),
                            'ref_pole' => $cmd->getRef_pole(),
                            'ref_offer' => $cmd->getRef_offre()
                        )
                    ));

                    $cmd->setOffres($offres);
                    break;
            }
        }
    }
}
