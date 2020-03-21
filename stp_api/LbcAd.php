<?php
namespace spamtonprof\stp_api;

class LbcAd implements \JsonSerializable
{

    protected $ref_ad, $body, $subject, $image_url, $name, $male, $category, $matiere, $period, $tadabase_id, $ready;

    /**
     *
     * @return mixed
     */
    public function getReady()
    {
        return $this->ready;
    }

    /**
     *
     * @param mixed $ready
     */
    public function setReady($ready)
    {
        $this->ready = $ready;
    }

    /**
     *
     * @return mixed
     */
    public function getTadabase_id()
    {
        return $this->tadabase_id;
    }

    /**
     *
     * @param mixed $tadabase_id
     */
    public function setTadabase_id($tadabase_id)
    {
        $this->tadabase_id = $tadabase_id;
    }

    /**
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return mixed
     */
    public function getMale()
    {
        return $this->male;
    }

    /**
     *
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     *
     * @return mixed
     */
    public function getMatiere()
    {
        return $this->matiere;
    }

    /**
     *
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     *
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     *
     * @param mixed $male
     */
    public function setMale($male)
    {
        $this->male = $male;
    }

    /**
     *
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     *
     * @param mixed $matiere
     */
    public function setMatiere($matiere)
    {
        $this->matiere = $matiere;
    }

    /**
     *
     * @param mixed $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
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

    public function getRef_ad()
    {
        return $this->ref_ad;
    }

    public function setRef_ad($ref_ad)
    {
        $this->ref_ad = $ref_ad;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getImage_url()
    {
        return $this->image_url;
    }

    public function setImage_url($image_url)
    {
        $this->image_url = $image_url;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}