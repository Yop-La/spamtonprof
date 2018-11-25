<?php
namespace spamtonprof\stp_api;

class StpNiveau implements \JsonSerializable
{

    protected $ref_niveau, $niveau, $sigle, $keyword, $parent_required, $gr_id;

    /**
     * @return mixed
     */
    public function getGr_id()
    {
        return $this->gr_id;
    }

    /**
     * @param mixed $gr_id
     */
    public function setGr_id($gr_id)
    {
        $this->gr_id = $gr_id;
    }

    /**
     * @return mixed
     */
    public function getParent_required()
    {
        return $this->parent_required;
    }

    /**
     * @param mixed $parent_required
     */
    public function setParent_required($parent_required)
    {
        $this->parent_required = $parent_required;
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

    public function getRef_niveau()
    {
        return $this->ref_niveau;
    }

    public function setRef_niveau($ref_niveau)
    {
        $this->ref_niveau = $ref_niveau;
    }

    public function getNiveau()
    {
        return $this->niveau;
    }

    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
    }

    public function getSigle()
    {
        return $this->sigle;
    }

    public function setSigle($sigle)
    {
        $this->sigle = $sigle;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}