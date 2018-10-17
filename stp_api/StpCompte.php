<?php
namespace spamtonprof\stp_api;

class StpCompte implements \JsonSerializable
{

    protected $ref_compte, $date_creation, $ref_proche, $stripe_client;

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);
    }

    /**
     * @return mixed
     */
    public function getStripe_client()
    {
        return $this->stripe_client;
    }

    /**
     * @param mixed $stripe_client
     */
    public function setStripe_client($stripe_client)
    {
        $this->stripe_client = $stripe_client;
    }

    public function hydrate(array $donnees)
    {
        foreach ($donnees as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    public static function cast(\spamtonprof\stp_api\StpCompte $compte)
    {
        return ($compte);
    }
    
    
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    public function getDate_creation()
    {
        return $this->date_creation;
    }

    public function setDate_creation( $date_creation)
    {
        $this->date_creation = $date_creation;
    }

    public function getRef_proche()
    {
        return $this->ref_proche;
    }

    public function setRef_proche($ref_proche)
    {
        $this->ref_proche = $ref_proche;
    }

    public function toArray()
    {
        $retour = [];
        
        foreach ($this as $key => $value) {
            $retour[$key] = $value;
        }
        return ($retour);
    }
 

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}