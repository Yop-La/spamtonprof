<?php
namespace spamtonprof\stp_api;

class LbcAdsImmo implements \JsonSerializable
{

    protected $ref_ad, $price, $square, $url, $first_publication_date, $city, $type_bien, $zipcode, $index_date, $room, $notified, $target, $date_retrait, $category, $text, $date_last_crawl;

    
    
    
    /**
     * @return mixed
     */
    public function getDate_last_crawl()
    {
        return $this->date_last_crawl;
    }

    /**
     * @param mixed $date_last_crawl
     */
    public function setDate_last_crawl($date_last_crawl)
    {
        $this->date_last_crawl = $date_last_crawl;
    }

    /**
     * 
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_retrait()
    {
        return $this->date_retrait;
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
     * @param mixed $date_retrait
     */
    public function setDate_retrait($date_retrait)
    {
        $this->date_retrait = $date_retrait;
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
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     *
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     *
     * @return mixed
     */
    public function getNotified()
    {
        return $this->notified;
    }

    /**
     *
     * @param mixed $notified
     */
    public function setNotified($notified)
    {
        $this->notified = $notified;
    }

    /**
     *
     * @return mixed
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     *
     * @param mixed $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     *
     * @return mixed
     */
    public function getIndex_date()
    {
        return $this->index_date;
    }

    /**
     *
     * @param mixed $index_date
     */
    public function setIndex_date($index_date)
    {
        $this->index_date = $index_date;
    }

    /**
     *
     * @return mixed
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     *
     * @param mixed $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     *
     * @return mixed
     */
    public function getType_bien()
    {
        return $this->type_bien;
    }

    /**
     *
     * @param mixed $type_bien
     */
    public function setType_bien($type_bien)
    {
        $this->type_bien = $type_bien;
    }

    /**
     *
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     *
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
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

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getSquare()
    {
        return $this->square;
    }

    public function setSquare($square)
    {
        $this->square = $square;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getFirst_publication_date()
    {
        return $this->first_publication_date;
    }

    public function setFirst_publication_date($first_publication_date)
    {
        $this->first_publication_date = $first_publication_date;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}