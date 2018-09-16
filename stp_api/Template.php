<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class LbcAccount implements \JsonSerializable
{

    protected $column_name,
    $ref_compte,$mail,
    $password,
    $nb_annonces_online,
    $date_dernier_control,
    $pseudo,
    $redirection,
    $date_derniere_activite,
    $date_avant_peremption,
    $disabled,
    $date_of_disabling,
    $ref_client,
    $pack_booster,
    $end_pack;

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

  
}

