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
    $ref_expe,
    $expe,
    $code_promo,
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
    /**
     * @return mixed
     */
    public function getColumn_name()
    {
        return $this->column_name;
    }

    /**
     * @return mixed
     */
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getNb_annonces_online()
    {
        return $this->nb_annonces_online;
    }

    /**
     * @return mixed
     */
    public function getDate_dernier_control()
    {
        return $this->date_dernier_control;
    }

    /**
     * @return mixed
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * @return mixed
     */
    public function getRedirection()
    {
        return $this->redirection;
    }

    /**
     * @return mixed
     */
    public function getDate_derniere_activite()
    {
        return $this->date_derniere_activite;
    }

    /**
     * @return mixed
     */
    public function getDate_avant_peremption()
    {
        return $this->date_avant_peremption;
    }

    /**
     * @return mixed
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @return mixed
     */
    public function getDate_of_disabling()
    {
        return $this->date_of_disabling;
    }

    /**
     * @return mixed
     */
    public function getRef_client()
    {
        return $this->ref_client;
    }

    /**
     * @return mixed
     */
    public function getPack_booster()
    {
        return $this->pack_booster;
    }

    /**
     * @return mixed
     */
    public function getEnd_pack()
    {
        return $this->end_pack;
    }

    /**
     * @param mixed $column_name
     */
    public function setColumn_name($column_name)
    {
        $this->column_name = $column_name;
    }

    /**
     * @param mixed $ref_compte
     */
    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param mixed $nb_annonces_online
     */
    public function setNb_annonces_online($nb_annonces_online)
    {
        $this->nb_annonces_online = $nb_annonces_online;
    }

    /**
     * @param mixed $date_dernier_control
     */
    public function setDate_dernier_control($date_dernier_control)
    {
        $this->date_dernier_control = $date_dernier_control;
    }

    /**
     * @param mixed $pseudo
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;
    }

    /**
     * @param mixed $redirection
     */
    public function setRedirection($redirection)
    {
        $this->redirection = $redirection;
    }

    /**
     * @param mixed $date_derniere_activite
     */
    public function setDate_derniere_activite($date_derniere_activite)
    {
        $this->date_derniere_activite = $date_derniere_activite;
    }

    /**
     * @param mixed $date_avant_peremption
     */
    public function setDate_avant_peremption($date_avant_peremption)
    {
        $this->date_avant_peremption = $date_avant_peremption;
    }

    /**
     * @param mixed $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     * @param mixed $date_of_disabling
     */
    public function setDate_of_disabling($date_of_disabling)
    {
        $this->date_of_disabling = $date_of_disabling;
    }

    /**
     * @param mixed $ref_client
     */
    public function setRef_client($ref_client)
    {
        $this->ref_client = $ref_client;
    }

    /**
     * @param mixed $pack_booster
     */
    public function setPack_booster($pack_booster)
    {
        $this->pack_booster = $pack_booster;
    }

    /**
     * @param mixed $end_pack
     */
    public function setEnd_pack($end_pack)
    {
        $this->end_pack = $end_pack;
    }
    /**
     * @return mixed
     */
    public function getRef_expe()
    {
        return $this->ref_expe;
    }

    /**
     * @param mixed $ref_expe
     */
    public function setRef_expe($ref_expe)
    {
        $this->ref_expe = $ref_expe;
        
        $expeMg = new \spamtonprof\stp_api\ExpeLbcManager();
        
        $this->expe = $expeMg -> get(array("ref_expe" => $ref_expe));
    }
    /**
     * @return mixed
     */
    public function getExpe()
    {
        return $this->expe;
    }

    /**
     * @param mixed $expe
     */
    public function setExpe($expe)
    {
        $this->expe = $expe;
    }
    /**
     * @return mixed
     */
    public function getCode_promo()
    {
        return $this->code_promo;
    }

    /**
     * @param mixed $code_promo
     */
    public function setCode_promo($code_promo)
    {
        $this->code_promo = $code_promo;
    }


    


    
    

  
}

