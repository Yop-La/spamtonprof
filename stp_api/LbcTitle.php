<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *        
 */
class LbcTitle implements \JsonSerializable
{

    protected $ref_titre, $titre, $type_titre, $ref_type_titre;

    /**
     *
     * @return mixed
     */
    public function getRef_type_titre()
    {
        return $this->ref_type_titre;
    }

    /**
     *
     * @param mixed $ref_type_titre
     */
    public function setRef_type_titre($ref_type_titre)
    {
        $this->ref_type_titre = $ref_type_titre;
    }

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

    /**
     *
     * @return mixed
     */
    public function getRef_titre()
    {
        return $this->ref_titre;
    }

    /**
     *
     * @return mixed
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     *
     * @return mixed
     */
    public function getType_titre()
    {
        return $this->type_titre;
    }

    /**
     *
     * @param mixed $ref_titre
     */
    public function setRef_titre($ref_titre)
    {
        $this->ref_titre = $ref_titre;
    }

    /**
     *
     * @param mixed $titre
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    /**
     *
     * @param mixed $type_titre
     */
    public function setType_titre($type_titre)
    {
        $this->type_titre = $type_titre;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
    }

    public function __toString()
    {
        return ($this->titre);
    }
}

