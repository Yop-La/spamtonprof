<?php
namespace spamtonprof\stp_api;

use PDO;

class MessageProspectLbcManager

{

    private $_db;

    // Instance de PDO
    public function __construct()

    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        // todostp faire pareil pour getresponse_api
    }

    public function getMessageToForward()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc where forwarded = false and labelled = true order by date_reception  limit 1");

        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }

    public function get_new_lead_messages()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc where forwarded = false and labelled = false order by date_reception  limit 1");

        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }

    // retourne les messages à qui on doit répondre
    public function get_message_to_reply()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc where ready_to_answer = true and ancien_prospect = false and message_reconnu = false and pseudo_reconnu = false and automatic_answer_done = false order by date_reception limit 1;");

        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }

    // retourne les messages à envoyés avec le serveur smtp ( la décision de répondre a déjà été prise )
    public function get_message_to_send()
    {
        $q = $this->_db->prepare("select * from message_prospect_lbc where to_send = true order by date_reception limit 1;");

        $q->execute();

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }

    public function getAll($info)
    {
        $data = false;
        $prospects = [];

        if (in_array("forwarded_messages", $info)) {
            $q = $this->_db->prepare("select * from message_prospect_lbc where ready_to_answer = false and in_agent_box = true limit 10");
            $q->execute();
        }
        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            $prospect = new \spamtonprof\stp_api\MessageProspectLbc($data);
            $prospects[] = $prospect;
        }
        return ($prospects);
    }

    public function get($info)
    {
        $data = false;
        if (array_key_exists("gmail_id", $info)) {
            $gmailId = $info["gmail_id"];
            $q = $this->_db->prepare("select * from message_prospect_lbc where gmail_id =:gmail_id");
            $q->bindValue(":gmail_id", $gmailId);
            $q->execute();

            $data = $q->fetch(PDO::FETCH_ASSOC);
        }
        if (array_key_exists("ref_message", $info)) {
            $refMessage = $info["ref_message"];
            $q = $this->_db->prepare("select * from message_prospect_lbc where ref_message =:ref_message");
            $q->bindValue(":ref_message", $refMessage, PDO::PARAM_INT);
            $q->execute();

            $data = $q->fetch(PDO::FETCH_ASSOC);
        }
        if (array_key_exists("prospect_existe", $info)) {

            $prospect_existe = $info["prospect_existe"];
            $ref_prospect_lbc = $prospect_existe['ref_prospect_lbc'];
            $ref_message = $prospect_existe['ref_message'];
            $q = $this->_db->prepare("select * from message_prospect_lbc where ref_prospect_lbc =:ref_prospect_lbc and ref_message < :ref_message");
            $q->bindValue(":ref_prospect_lbc", $ref_prospect_lbc);
            $q->bindValue(":ref_message", $ref_message);
            $q->execute();
            $data = $q->fetch(PDO::FETCH_ASSOC);
        }

        if (array_key_exists("pseudo_existe", $info)) {
            $pseudo_existe = $info["pseudo_existe"];
            $pseudo = $pseudo_existe['pseudo'];
            $date_limite = $pseudo_existe['date_limite'];
            $ref_message = $pseudo_existe['ref_message'];
            $q = $this->_db->prepare("select * from message_prospect_lbc where lower(pseudo) =lower(:pseudo) and ref_message < :ref_message and date_reception >= :date_limite");
            $q->bindValue(":pseudo", $pseudo);
            $q->bindValue(":ref_message", $ref_message);
            $q->bindValue(":date_limite", $date_limite);
            $q->execute();
            $data = $q->fetch(PDO::FETCH_ASSOC);
        }

        if (array_key_exists("body_existe", $info)) {

            $body_existe = $info["body_existe"];
            $body = $body_existe['body'];
            $ref_message = $body_existe['ref_message'];
            $date_limite = $body_existe['date_limite'];

            $q = $this->_db->prepare("select * from message_prospect_lbc where levenshtein(lower(body), lower(:body)) < 5 and ref_message < :ref_message and date_reception >= :date_limite");
            $q->bindValue(":body", $body);
            $q->bindValue(":ref_message", $ref_message);
            $q->bindValue(":date_limite", $date_limite);
            $q->execute();
            $data = $q->fetch(PDO::FETCH_ASSOC);
        }
        if (! $data) {
            return false;
        }
        return new \spamtonprof\stp_api\MessageProspectLbc($data);
    }

    public function update_pseudo_reconnu(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set pseudo_reconnu = :pseudo_reconnu where ref_message = :ref_message ");
        $q->bindValue(":pseudo_reconnu", $messageProspectLbc->getPseudo_reconnu(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_message_reconnu(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set message_reconnu = :message_reconnu where ref_message = :ref_message ");
        $q->bindValue(":message_reconnu", $messageProspectLbc->getMessage_reconnu(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_in_agent_box(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set in_agent_box = :in_agent_box where ref_message = :ref_message ");
        $q->bindValue(":in_agent_box", $messageProspectLbc->getIn_agent_box(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_gmail_id_bureau_prof(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set gmail_id_bureau_prof = :gmail_id_bureau_prof where ref_message = :ref_message ");
        $q->bindValue(":gmail_id_bureau_prof", $messageProspectLbc->getGmail_id_bureau_prof());
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_ancien_prospect(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set ancien_prospect = :ancien_prospect where ref_message = :ref_message ");
        $q->bindValue(":ancien_prospect", $messageProspectLbc->getAncien_prospect(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_ready_to_answer(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set ready_to_answer = :ready_to_answer where ref_message = :ref_message ");
        $q->bindValue(":ready_to_answer", $messageProspectLbc->getReady_to_answer(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_labelled(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set labelled = :labelled where ref_message = :ref_message ");
        $q->bindValue(":labelled", $messageProspectLbc->getLabelled(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_forwarded(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set forwarded = :forwarded where ref_message = :ref_message ");
        $q->bindValue(":forwarded", $messageProspectLbc->getForwarded(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_automatic_answer_done(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set automatic_answer_done = :automatic_answer_done where ref_message = :ref_message ");
        $q->bindValue(":automatic_answer_done", $messageProspectLbc->getAutomatic_answer_done(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function update_to_send(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set to_send = :to_send where ref_message = :ref_message ");
        $q->bindValue(":to_send", $messageProspectLbc->getTo_send(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function updateReply(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set reply = :reply where ref_message = :ref_message ");
        $q->bindValue(":reply", $messageProspectLbc->getReply());
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function updateAnswerGmailId(\spamtonprof\stp_api\MessageProspectLbc $messageProspectLbc)
    {
        $q = $this->_db->prepare("update message_prospect_lbc set answer_gmail_id = :answer_gmail_id where ref_message = :ref_message ");
        $q->bindValue(":answer_gmail_id", $messageProspectLbc->getAnswer_gmail_id());
        $q->bindValue(":ref_message", $messageProspectLbc->getRef_message());
        $q->execute();
    }

    public function add(MessageProspectLbc $message)
    {
        $q = $this->_db->prepare('INSERT INTO message_prospect_lbc(date_reception, ref_compte_lbc, ref_prospect_lbc, gmail_id, subject, type, ancien_prospect, message_reconnu, pseudo_reconnu, pseudo,body,labelled, forwarded, in_agent_box, ready_to_answer, to_send, automatic_answer_done) VALUES(:date_reception, :ref_compte_lbc, :ref_prospect_lbc, :gmail_id, :subject, :type, false, false, false, :pseudo,:body,false,false,false,false,false,false)');
        $q->bindValue(':date_reception', $message->getDate_reception()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':ref_compte_lbc', $message->getRef_compte_lbc());
        $q->bindValue(':ref_prospect_lbc', $message->getRef_prospect_lbc());
        $q->bindValue(':gmail_id', $message->getGmail_id());
        $q->bindValue(':subject', $message->getSubject());
        $q->bindValue(':type', $message->getType(), PDO::PARAM_INT);

        $q->bindValue(':body', $message->getBody());
        $q->bindValue(':pseudo', $message->getPseudo());

        $q->execute();

        $message->setRef_message($this->_db->lastInsertId());
        return ($message);
    }
}