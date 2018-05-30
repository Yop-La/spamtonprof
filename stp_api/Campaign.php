<?php

namespace spamtonprof\stp_api;

class Campaign  implements \JsonSerializable

{

  private $ref_campaign,

            $ref_campaign_get_response,

            $nom_campaign;
  
            
  public function __construct(array $donnees)

  {

    $this->hydrate($donnees);
    
    
    
  }

  

  public function hydrate(array $donnees)

  {

    foreach ($donnees as $key => $value)

    {

      $method = 'set'.ucfirst($key);


      if (method_exists($this, $method))

      {
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
    public function ref_campaign()
    {
        return $this->ref_campaign;
    }

/**
     * @return mixed
     */
    public function ref_campaign_get_response()
    {
        return $this->ref_campaign_get_response;
    }

/**
     * @return mixed
     */
    public function nom_campaign()
    {
        return $this->nom_campaign;
    }

/**
     * @param mixed $ref_campaign
     */
    public function setRef_campaign($ref_campaign)
    {
        $this->ref_campaign = $ref_campaign;
    }

/**
     * @param mixed $ref_campaign_get_response
     */
    public function setRef_campaign_get_response($ref_campaign_get_response)
    {
        $this->ref_campaign_get_response = $ref_campaign_get_response;
    }

/**
     * @param mixed $nom_campaign
     */
    public function setNom_campaign($nom_campaign)
    {
        $this->nom_campaign = $nom_campaign;
    }


  


}