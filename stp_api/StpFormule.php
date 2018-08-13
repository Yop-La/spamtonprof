<?php
namespace spamtonprof\stp_api;

class StpFormule implements \JsonSerializable
{

    protected $formule, $ref_formule, $matieres;

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
    public function getFormule()
    {
        return $this->formule;
    }

    /**
     *
     * @param mixed $formule
     */
    public function setFormule($formule)
    {
        $this->formule = $formule;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

    /**
     *
     * @return mixed
     */
    public function getRef_formule()
    {
        return $this->ref_formule;
    }

    /**
     *
     * @param mixed $ref_formule
     */
    public function setRef_formule($ref_formule)
    {
        $this->ref_formule = $ref_formule;
    }

    public static function cast($formule): \spamtonprof\stp_api\StpFormule
    {
        return ($formule);
    }

    /**
     *
     * @return mixed
     */
    public function getMatieres()
    {
        if (gettype($this->matieres) == "string") {
            $matieres = str_replace(array(
                '{',
                '}'
            ), array(
                ''
            ), $this->matieres);
            $matieres = explode(",", $matieres);
            $this->matieres = $matieres;
        }
        return $this->matieres;
    }

    /**
     *
     * @param mixed $matieres
     */
    public function setMatieres($matieres)
    {
        $this->matieres = $matieres;
    }

    // pour retourner le custom field mati�res de getresponse ( sequence essai )
    public function toGetResponse()
    {
        $matieres = $this->getMatieres();
        for ($i = 0; $i < count($matieres); $i ++) {
            $matiere = $matieres[$i];
            if ($matiere == "francais") {
                $matieres[$i] = "fran�ais";
            }
        }
        $matieres = implode("_", $matieres);
        return (utf8_encode($matieres));
    }
    
}

