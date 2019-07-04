<?php
namespace spamtonprof\stp_api;

class LbcCampaign implements \JsonSerializable
{

    protected $ref_campaign, $date, $ref_compte, $nb_ad_online, $nb_ad_publie;

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

    public function getRef_campaign()
    {
        return $this->ref_campaign;
    }

    public function setRef_campaign($ref_campaign)
    {
        $this->ref_campaign = $ref_campaign;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    public function getNb_ad_online()
    {
        return $this->nb_ad_online;
    }

    public function setNb_ad_online($nb_ad_online)
    {
        $this->nb_ad_online = $nb_ad_online;
    }

    public function getNb_ad_publie()
    {
        return $this->nb_ad_publie;
    }

    public function setNb_ad_publie($nb_ad_publie)
    {
        $this->nb_ad_publie = $nb_ad_publie;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}