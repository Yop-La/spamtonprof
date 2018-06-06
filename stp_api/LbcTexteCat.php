<?php
namespace spamtonprof\stp_api;


/**
 *
 * @author alexg
 *        
 */
class LbcTexteCat implements \JsonSerializable
{

    protected $ref_texte_cat, $nom_cat, $nb_paragraph, $nb_texte;

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
     * @return mixed
     */
    public function getRef_texte_cat()
    {
        return $this->ref_texte_cat;
    }

    /**
     * @return mixed
     */
    public function getNom_cat()
    {
        return $this->nom_cat;
    }

    /**
     * @return mixed
     */
    public function getNb_paragraph()
    {
        return $this->nb_paragraph;
    }

    /**
     * @return mixed
     */
    public function getNb_texte()
    {
        return $this->nb_texte;
    }

    /**
     * @param mixed $ref_texte_cat
     */
    public function setRef_texte_cat($ref_texte_cat)
    {
        $this->ref_texte_cat = $ref_texte_cat;
    }

    /**
     * @param mixed $nom_cat
     */
    public function setNom_cat($nom_cat)
    {
        $this->nom_cat = $nom_cat;
    }

    /**
     * @param mixed $nb_paragraph
     */
    public function setNb_paragraph($nb_paragraph)
    {
        $this->nb_paragraph = $nb_paragraph;
    }

    /**
     * @param mixed $nb_texte
     */
    public function setNb_texte($nb_texte)
    {
        $this->nb_texte = $nb_texte;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
}

