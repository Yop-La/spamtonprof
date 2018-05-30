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
    $is_sent,
    $ref_compte_lbc,
    $gmail_id;
    

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
    public function getIs_sent()
    {
        return $this->is_sent;
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

    /**
     * @param mixed $is_sent
     */
    public function setIs_sent($is_sent)
    {
        $this->is_sent = $is_sent;
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


    

    

    
  
}

