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

    protected 
    $ref_message,
    $date_reception,
    $ref_prospect_lbc,
    $processed,
    $type,
    $ref_compte_lbc,
    $subject,
    $gmail_id,
    $answered,
    $reply,
    $answer_gmail_id;
    

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
     * @return mixed
     */
    public function getRef_message()
    {
        return $this->ref_message;
    }

    /**
     * @return mixed
     */
    public function getDate_reception()
    {
        return $this->date_reception;
    }


    /**
     * @return mixed
     */
    public function getProcessed()
    {
        return $this->processed;
    }


    /**
     * @param mixed $ref_message
     */
    public function setRef_message($ref_message)
    {
        $this->ref_message = $ref_message;
    }

    /**
     * @param mixed $date_reception
     */
    public function setDate_reception($date_reception)
    {
        if (gettype($date_reception) == "string") {
            
            $date_reception = new \DateTime($date_reception, new \DateTimeZone("Europe/Paris"));
        
        }
        
        $this->date_reception = $date_reception;
    }

    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }


    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
    /**
     * @return mixed
     */
    public function getRef_compte_lbc()
    {
        return $this->ref_compte_lbc;
    }
    /**
     * @return mixed
     */
    public function getRef_prospect_lbc()
    {
        return $this->ref_prospect_lbc;
    }

    /**
     * @param mixed $ref_prospect_lbc
     */
    public function setRef_prospect_lbc($ref_prospect_lbc)
    {
        $this->ref_prospect_lbc = $ref_prospect_lbc;
    }

    /**
     * @param mixed $ref_compte_lbc
     */
    public function setRef_compte_lbc($ref_compte_lbc)
    {
        $this->ref_compte_lbc = $ref_compte_lbc;
    }
    /**
     * @return mixed
     */
    public function getGmail_id()
    {
        return $this->gmail_id;
    }

    /**
     * @param mixed $gmail_id
     */
    public function setGmail_id($gmail_id)
    {
        $this->gmail_id = $gmail_id;
    }
    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    /**
     * @return mixed
     */
    public function getAnswered()
    {
        return $this->answered;
    }

    /**
     * @param mixed $answered
     */
    public function setAnswered($answered)
    {
        $this->answered = $answered;
    }
    /**
     * @return mixed
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * @param mixed $reply
     */
    public function setReply($reply)
    {
        $this->reply = $reply;
    }
    /**
     * @return mixed
     */
    public function getAnswer_gmail_id()
    {
        return $this->answer_gmail_id;
    }

    /**
     * @param mixed $answer_gmail_id
     */
    public function setAnswer_gmail_id($answer_gmail_id)
    {
        $this->answer_gmail_id = $answer_gmail_id;
    }







    

    

    
  
}

