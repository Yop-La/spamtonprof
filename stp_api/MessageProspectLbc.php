<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class MessageProspectLbc implements \JsonSerializable
{

    protected $ref_message, $date_reception, $ref_prospect_lbc, $type, $ref_compte_lbc, $subject, $gmail_id, $reply, $answer_gmail_id, $ancien_prospect, $message_reconnu, $pseudo_reconnu, $pseudo, $body, $labelled, $forwarded, $in_agent_box, $ready_to_answer, $gmail_id_bureau_prof, $to_send, $automatic_answer_done;

    /**
     *
     * @return mixed
     */
    public function getAutomatic_answer_done()
    {
        return $this->automatic_answer_done;
    }

    /**
     *
     * @return mixed
     */
    public function getAnswered()
    {
        return $this->answered;
    }

    /**
     *
     * @param mixed $automatic_answer_done
     */
    public function setAutomatic_answer_done($automatic_answer_done)
    {
        $this->automatic_answer_done = $automatic_answer_done;
    }

    /**
     *
     * @return mixed
     */
    public function getTo_send()
    {
        return $this->to_send;
    }

    public static function cast(\spamtonprof\stp_api\MessageProspectLbc $mess)
    {
        return ($mess);
    }

    /**
     *
     * @param mixed $to_send
     */
    public function setTo_send($to_send)
    {
        $this->to_send = $to_send;
    }

    /**
     *
     * @return mixed
     */
    public function getGmail_id_bureau_prof()
    {
        return $this->gmail_id_bureau_prof;
    }

    /**
     *
     * @param mixed $gmail_id_bureau_prof
     */
    public function setGmail_id_bureau_prof($gmail_id_bureau_prof)
    {
        $this->gmail_id_bureau_prof = $gmail_id_bureau_prof;
    }

    /**
     *
     * @return mixed
     */
    public function getReady_to_answer()
    {
        return $this->ready_to_answer;
    }

    /**
     *
     * @param mixed $ready_to_answer
     */
    public function setReady_to_answer($ready_to_answer)
    {
        $this->ready_to_answer = $ready_to_answer;
    }

    /**
     *
     * @return mixed
     */
    public function getIn_agent_box()
    {
        return $this->in_agent_box;
    }

    /**
     *
     * @param mixed $in_agent_box
     */
    public function setIn_agent_box($in_agent_box)
    {
        $this->in_agent_box = $in_agent_box;
    }

    /**
     *
     * @return mixed
     */
    public function getLabelled()
    {
        return $this->labelled;
    }

    /**
     *
     * @return mixed
     */
    public function getForwarded()
    {
        return $this->forwarded;
    }

    /**
     *
     * @param mixed $labelled
     */
    public function setLabelled($labelled)
    {
        $this->labelled = $labelled;
    }

    /**
     *
     * @param mixed $forwarded
     */
    public function setForwarded($forwarded)
    {
        $this->forwarded = $forwarded;
    }

    /**
     *
     * @return mixed
     */
    public function getAncien_prospect()
    {
        return $this->ancien_prospect;
    }

    /**
     *
     * @return mixed
     */
    public function getPseudo_reconnu()
    {
        return $this->pseudo_reconnu;
    }

    /**
     *
     * @return mixed
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     *
     * @param mixed $ancien_prospect
     */
    public function setAncien_prospect($ancien_prospect)
    {
        $this->ancien_prospect = $ancien_prospect;
    }

    /**
     *
     * @return mixed
     */
    public function getMessage_reconnu()
    {
        return $this->message_reconnu;
    }

    /**
     *
     * @param mixed $message_reconnu
     */
    public function setMessage_reconnu($message_reconnu)
    {
        $this->message_reconnu = $message_reconnu;
    }

    /**
     *
     * @param mixed $pseudo_reconnu
     */
    public function setPseudo_reconnu($pseudo_reconnu)
    {
        $this->pseudo_reconnu = $pseudo_reconnu;
    }

    /**
     *
     * @param mixed $pseudo
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;
    }

    /**
     *
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    public function __construct(array $donnees = array())

    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)

    {
        foreach ($donnees as $key => $value) {

            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {

                $this->$method($value);
            }
        }
    }

    /**
     *
     * @return mixed
     */
    public function getRef_message()
    {
        return $this->ref_message;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_reception()
    {
        return $this->date_reception;
    }

    /**
     *
     * @param mixed $ref_message
     */
    public function setRef_message($ref_message)
    {
        $this->ref_message = $ref_message;
    }

    /**
     *
     * @param mixed $date_reception
     */
    public function setDate_reception($date_reception)
    {
        if (gettype($date_reception) == "string") {

            $date_reception = new \DateTime($date_reception, new \DateTimeZone("Europe/Paris"));
        }

        $this->date_reception = $date_reception;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_compte_lbc()
    {
        return $this->ref_compte_lbc;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_prospect_lbc()
    {
        return $this->ref_prospect_lbc;
    }

    /**
     *
     * @param mixed $ref_prospect_lbc
     */
    public function setRef_prospect_lbc($ref_prospect_lbc)
    {
        $this->ref_prospect_lbc = $ref_prospect_lbc;
    }

    /**
     *
     * @param mixed $ref_compte_lbc
     */
    public function setRef_compte_lbc($ref_compte_lbc)
    {
        $this->ref_compte_lbc = $ref_compte_lbc;
    }

    /**
     *
     * @return mixed
     */
    public function getGmail_id()
    {
        return $this->gmail_id;
    }

    /**
     *
     * @param mixed $gmail_id
     */
    public function setGmail_id($gmail_id)
    {
        $this->gmail_id = $gmail_id;
    }

    /**
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     *
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     *
     * @return mixed
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     *
     * @param mixed $reply
     */
    public function setReply($reply)
    {
        $this->reply = $reply;
    }

    /**
     *
     * @return mixed
     */
    public function getAnswer_gmail_id()
    {
        return $this->answer_gmail_id;
    }

    /**
     *
     * @param mixed $answer_gmail_id
     */
    public function setAnswer_gmail_id($answer_gmail_id)
    {
        $this->answer_gmail_id = $answer_gmail_id;
    }
}

