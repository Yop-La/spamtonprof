<?php
namespace spamtonprof\stp_api;

class GmailLabel implements \JsonSerializable

{

    protected $ref_label, $nom_label, $color_label, $action, $type;

    /**
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function __construct(array $donnees)

    {
        $this->hydrate($donnees);
    }

    /**
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     *
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
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
    public function getRef_label()
    {
        return $this->ref_label;
    }

    /**
     *
     * @return mixed
     */
    public function getNom_label()
    {
        return $this->nom_label;
    }

    /**
     *
     * @return mixed
     */
    public function getColor_label()
    {
        return $this->color_label;
    }

    /**
     *
     * @param mixed $ref_label
     */
    public function setRef_label($ref_label)
    {
        $this->ref_label = $ref_label;
    }

    /**
     *
     * @param mixed $nom_label
     */
    public function setNom_label($nom_label)
    {
        $this->nom_label = $nom_label;
    }

    /**
     *
     * @param mixed $color_label
     */
    public function setColor_label($color_label)
    {
        $this->color_label = $color_label;
    }

    public function jsonSerialize()

    {
        $vars = get_object_vars($this);

        return $vars;
    }
}