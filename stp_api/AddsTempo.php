<?php
namespace spamtonprof\stp_api;

class addsTempo implements \JsonSerializable
{

    protected $first_publication_date, $zipcode, $city, $id, $ref_compte, $has_phone, $ref_commune;

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

    /**
     *
     * @return mixed
     */
    public function getRef_commune()
    {
        return $this->ref_commune;
    }

    /**
     *
     * @param mixed $ref_commune
     */
    public function setRef_commune($ref_commune)
    {
        $this->ref_commune = $ref_commune;
    }

    public function getFirst_publication_date()
    {
        return $this->first_publication_date;
    }

    public function setFirst_publication_date($first_publication_date)
    {
        $this->first_publication_date = $first_publication_date;
    }

    public function getZipcode()
    {
        return $this->zipcode;
    }

    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    public function getHas_phone()
    {
        return $this->has_phone;
    }

    public function setHas_phone($has_phone)
    {
        $this->has_phone = $has_phone;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}