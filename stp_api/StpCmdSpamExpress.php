<?php
namespace spamtonprof\stp_api;

class StpCmdSpamExpress implements \JsonSerializable
{

    protected $ref_cmd, $ref_lead, $ref_cat_scolaire, $status, $ref_pole, $lead, $offres, $ref_offre, $remarque, $pole, $cat_scolaire, $ref_prof, $prof, $offre, $payment_intent_id, $transfert_id;

    /**
     *
     * @return mixed
     */
    public function getPayment_intent_id()
    {
        return $this->payment_intent_id;
    }

    /**
     *
     * @return mixed
     */
    public function getTransfert_id()
    {
        return $this->transfert_id;
    }

    /**
     *
     * @param mixed $payment_intent_id
     */
    public function setPayment_intent_id($payment_intent_id)
    {
        $this->payment_intent_id = $payment_intent_id;
    }

    /**
     *
     * @param mixed $transfert_id
     */
    public function setTransfert_id($transfert_id)
    {
        $this->transfert_id = $transfert_id;
    }

    /**
     *
     * @return mixed
     */
    public function getOffre()
    {
        return $this->offre;
    }

    /**
     *
     * @param mixed $offre
     */
    public function setOffre(\spamtonprof\stp_api\StpOffreSpamExpress $offre)
    {
        $this->offre = $offre;
    }

    /**
     *
     * @return mixed
     */
    public function getProf()
    {
        return $this->prof;
    }

    /**
     *
     * @param mixed $prof
     */
    public function setProf(\spamtonprof\stp_api\StpProf $prof)
    {
        $this->prof = $prof;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_prof()
    {
        return $this->ref_prof;
    }

    /**
     *
     * @param mixed $ref_prof
     */
    public function setRef_prof($ref_prof)
    {
        $this->ref_prof = $ref_prof;
    }

    /**
     *
     * @return mixed
     */
    public function getPole()
    {
        return $this->pole;
    }

    /**
     *
     * @return mixed
     */
    public function getCat_scolaire()
    {
        return $this->cat_scolaire;
    }

    /**
     *
     * @param mixed $pole
     */
    public function setPole(\spamtonprof\stp_api\StpPole $pole)
    {
        $this->pole = $pole;
    }

    /**
     *
     * @param mixed $cat_scolaire
     */
    public function setCat_scolaire($cat_scolaire)
    {
        $this->cat_scolaire = $cat_scolaire;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_offre()
    {
        return $this->ref_offre;
    }

    /**
     *
     * @return mixed
     */
    public function getRemarque()
    {
        return $this->remarque;
    }

    /**
     *
     * @param mixed $ref_offre
     */
    public function setRef_offre($ref_offre)
    {
        $this->ref_offre = $ref_offre;
    }

    /**
     *
     * @param mixed $remarque
     */
    public function setRemarque($remarque)
    {
        $this->remarque = $remarque;
    }

    /**
     *
     * @return mixed
     */
    public function getOffres()
    {
        return $this->offres;
    }

    /**
     *
     * @param mixed $offres
     */
    public function setOffres($offres)
    {
        $this->offres = $offres;
    }

    /**
     *
     * @return mixed
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     *
     * @param mixed $lead
     */
    public function setLead(\spamtonprof\stp_api\StpLeadSpamExpress $lead)
    {
        $this->lead = $lead;
    }

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

    public function getRef_cmd()
    {
        return $this->ref_cmd;
    }

    public function setRef_cmd($ref_cmd)
    {
        $this->ref_cmd = $ref_cmd;
    }

    public function getRef_lead()
    {
        return $this->ref_lead;
    }

    public function setRef_lead($ref_lead)
    {
        $this->ref_lead = $ref_lead;
    }

    public function getRef_cat_scolaire()
    {
        return $this->ref_cat_scolaire;
    }

    public function setRef_cat_scolaire($ref_cat_scolaire)
    {
        $this->ref_cat_scolaire = $ref_cat_scolaire;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getRef_pole()
    {
        return $this->ref_pole;
    }

    public function setRef_pole($ref_pole)
    {
        $this->ref_pole = $ref_pole;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}