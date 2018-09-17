<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class MailForLead implements \JsonSerializable
{

    protected $ref_mail_for_lead,
    $body,
    $subject;

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
   
      public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
    /**
     * @return mixed
     */
    public function getRef_mail_for_lead()
    {
        return $this->ref_mail_for_lead;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $ref_mail_for_lead
     */
    public function setRef_mail_for_lead($ref_mail_for_lead)
    {
        $this->ref_mail_for_lead = $ref_mail_for_lead;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    
    

  
}

