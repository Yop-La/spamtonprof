<?php
namespace spamtonprof\stripe;

/**
 *
 * @author alexg
 *        
 */
class Subscription implements \JsonSerializable
{

    protected $id, $objectID, $status, $refCompte, $refAbonnement, $stripeProdId, $dateCreation;

    public function __construct($sub)

    {
        $this->objectID = $sub->id;
        $this->id = $sub->id;

        // pour stocker la date de cr�ation
        $dateCreation = new \DateTime(null);
        $dateCreation->setTimestamp($sub->created);
        $this->dateCreation = $dateCreation->format(PG_DATETIME_FORMAT);

        $this->status = $sub->status;
        $metadata = json_decode(json_encode($sub->metadata), true);

        if (array_key_exists("ref_compte", $metadata)) {
            $this->refCompte = $metadata["ref_compte"];
        }
        if (array_key_exists("ref_abonnement", $metadata)) {
            $this->refAbonnement = $metadata["ref_abonnement"];
        }
        if (array_key_exists("stripe_prof_id", $metadata)) {
            $this->stripeProdId = $metadata["stripe_prof_id"];
        }
    }

    /**
     *
     * @return mixed
     */
    public function getRefCompte()
    {
        return $this->refCompte;
    }

    public function toAlgoliaFormat()
    {
        $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

        if ($this->getRefAbonnement()) {

            $constructor = array(
                "construct" => array(
                    'ref_eleve'
                )
            );

            $stpAbo = $aboMg->get(array(
                "ref_abonnement" => $this->getRefAbonnement()
            ), $constructor);

            $eleve = $stpAbo->getEleve();
            $this->prenom = $eleve->getPrenom() . " " . $eleve->getNom();
        }
    }

    /**
     *
     * @return mixed
     */
    public function getRefAbonnement()
    {
        return $this->refAbonnement;
    }

    /**
     *
     * @return mixed
     */
    public function getStripeProdId()
    {
        return $this->stripeProdId;
    }

    /**
     *
     * @param mixed $refCompte
     */
    public function setRefCompte($refCompte)
    {
        $this->refCompte = $refCompte;
    }

    /**
     *
     * @param mixed $refAbonnement
     */
    public function setRefAbonnement($refAbonnement)
    {
        $this->refAbonnement = $refAbonnement;
    }

    /**
     *
     * @param mixed $stripeProdId
     */
    public function setStripeProdId($stripeProdId)
    {
        $this->stripeProdId = $stripeProdId;
    }

    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return mixed
     */
    public function getObjectID()
    {
        return $this->objectID;
    }

    /**
     *
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     *
     * @param mixed $objectID
     */
    public function setObjectID($objectID)
    {
        $this->objectID = $objectID;
    }

    /**
     *
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     *
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     *
     * @param mixed $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }
}

