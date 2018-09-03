<?php
namespace spamtonprof\stp_api;

class compteLbc implements \JsonSerializable
{

    protected $ref_compte, $mail, $password, $nb_annonces_online, $date_dernier_control, $pseudo, $redirection, $date_derniere_activite, $date_avant_peremption, $disabled, $date_of_disabling, $ref_client, $pack_booster, $end_pack, $ref_expe, $code_promo, $telephone;

    public function __construct(array $donnees = array())
    {
        $this->hydrate($donnees);
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

    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getNb_annonces_online()
    {
        return $this->nb_annonces_online;
    }

    public function setNb_annonces_online($nb_annonces_online)
    {
        $this->nb_annonces_online = $nb_annonces_online;
    }

    public function getDate_dernier_control()
    {
        return $this->date_dernier_control;
    }

    public function setDate_dernier_control($date_dernier_control)
    {
        $this->date_dernier_control = $date_dernier_control;
    }

    public function getPseudo()
    {
        return $this->pseudo;
    }

    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;
    }

    public function getRedirection()
    {
        return $this->redirection;
    }

    public function setRedirection($redirection)
    {
        $this->redirection = $redirection;
    }

    public function getDate_derniere_activite()
    {
        return $this->date_derniere_activite;
    }

    public function setDate_derniere_activite($date_derniere_activite)
    {
        $this->date_derniere_activite = $date_derniere_activite;
    }

    public function getDate_avant_peremption()
    {
        return $this->date_avant_peremption;
    }

    public function setDate_avant_peremption($date_avant_peremption)
    {
        $this->date_avant_peremption = $date_avant_peremption;
    }

    public function getDisabled()
    {
        return $this->disabled;
    }

    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    public function getDate_of_disabling()
    {
        return $this->date_of_disabling;
    }

    public function setDate_of_disabling($date_of_disabling)
    {
        $this->date_of_disabling = $date_of_disabling;
    }

    public function getRef_client()
    {
        return $this->ref_client;
    }

    public function setRef_client($ref_client)
    {
        $this->ref_client = $ref_client;
    }

    public function getPack_booster()
    {
        return $this->pack_booster;
    }

    public function setPack_booster($pack_booster)
    {
        $this->pack_booster = $pack_booster;
    }

    public function getEnd_pack()
    {
        return $this->end_pack;
    }

    public function setEnd_pack($end_pack)
    {
        $this->end_pack = $end_pack;
    }

    public function getRef_expe()
    {
        return $this->ref_expe;
    }

    public function setRef_expe($ref_expe)
    {
        $this->ref_expe = $ref_expe;
    }

    public function getCode_promo()
    {
        return $this->code_promo;
    }

    public function setCode_promo($code_promo)
    {
        $this->code_promo = $code_promo;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}