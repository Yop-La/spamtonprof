<?php
namespace spamtonprof\stp_api;

class Prof extends Personne implements \JsonSerializable
{

    private $ref_prof, 
    $gmail_credential,
    $gmail_adress;

    public function __construct(array $donnees)
    
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
    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    /**
     * @return mixed
     */
    public function getGmail_adress()
    {
        return $this->gmail_adress;
    }

    /**
     * @param mixed $ref_prof
     */
    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    /**
     * @param mixed $gmail_adress
     */
    public function setGmail_adress($gmail_adress)
    {
        $this->gmail_adress = $gmail_adress;
    }

    /**
     * @return mixed
     */
    public function getGmail_credential()
    {
        return $this->gmail_credential;
    }

    /**
     * @param mixed $gmail_credential
     */
    public function setGmail_credential($gmail_credential)
    {
        $this->gmail_credential = $gmail_credential;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}