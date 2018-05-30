<?php
namespace spamtonprof\stp_api;

use Exception;

/**
 *
 * @author alexg
 *         
 */
class AddLbc implements \JsonSerializable
{

    protected $ref_add,
    $nb_vues,
    $nb_clic_tel,
    $nb_mails,
    $date_mise_en_ligne,
    $nb_jours_restants,
    $ref_commune,
    $ref_titre,
    $ref_compte,
    $ref_texte,
    $nb_controls,
    $date_controle,
    $etat,
    $date_publication,
    $commune_soumise;
    
    
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
    public function getRef_add()
    {
        return $this->ref_add;
    }

    /**
     * @return mixed
     */
    public function getNb_vues()
    {
        return $this->nb_vues;
    }

    /**
     * @return mixed
     */
    public function getNb_clic_tel()
    {
        return $this->nb_clic_tel;
    }

    /**
     * @return mixed
     */
    public function getNb_mails()
    {
        return $this->nb_mails;
    }

    /**
     * @return mixed
     */
    public function getDate_mise_en_ligne()
    {
        return $this->date_mise_en_ligne;
    }

    /**
     * @return mixed
     */
    public function getNb_jours_restants()
    {
        return $this->nb_jours_restants;
    }

    /**
     * @return mixed
     */
    public function getRef_commune()
    {
        return $this->ref_commune;
    }

    /**
     * @return mixed
     */
    public function getRef_titre()
    {
        return $this->ref_titre;
    }

    /**
     * @return mixed
     */
    public function getRef_compte()
    {
        return $this->ref_compte;
    }

    /**
     * @return mixed
     */
    public function getRef_texte()
    {
        return $this->ref_texte;
    }

    /**
     * @return mixed
     */
    public function getNb_controls()
    {
        return $this->nb_controls;
    }

    /**
     * @return mixed
     */
    public function getDate_controle()
    {
        return $this->date_controle;
    }

    /**
     * @return mixed
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * @return mixed
     */
    public function getDate_publication()
    {
        return $this->date_publication;
    }

    /**
     * @return mixed
     */
    public function getCommune_soumise()
    {
        return $this->commune_soumise;
    }

    /**
     * @param mixed $ref_add
     */
    public function setRef_add($ref_add)
    {
        $this->ref_add = $ref_add;
    }

    /**
     * @param mixed $nb_vues
     */
    public function setNb_vues($nb_vues)
    {
        $this->nb_vues = $nb_vues;
    }

    /**
     * @param mixed $nb_clic_tel
     */
    public function setNb_clic_tel($nb_clic_tel)
    {
        $this->nb_clic_tel = $nb_clic_tel;
    }

    /**
     * @param mixed $nb_mails
     */
    public function setNb_mails($nb_mails)
    {
        $this->nb_mails = $nb_mails;
    }

    /**
     * @param mixed $date_mise_en_ligne
     */
    public function setDate_mise_en_ligne($date_mise_en_ligne)
    {
        $this->date_mise_en_ligne = $date_mise_en_ligne;
    }

    /**
     * @param mixed $nb_jours_restants
     */
    public function setNb_jours_restants($nb_jours_restants)
    {
        $this->nb_jours_restants = $nb_jours_restants;
    }

    /**
     * @param mixed $ref_commune
     */
    public function setRef_commune($ref_commune)
    {
        $this->ref_commune = $ref_commune;
    }

    /**
     * @param mixed $ref_titre
     */
    public function setRef_titre($ref_titre)
    {
        $this->ref_titre = $ref_titre;
    }

    /**
     * @param mixed $ref_compte
     */
    public function setRef_compte($ref_compte)
    {
        $this->ref_compte = $ref_compte;
    }

    /**
     * @param mixed $ref_texte
     */
    public function setRef_texte($ref_texte)
    {
        $this->ref_texte = $ref_texte;
    }

    /**
     * @param mixed $nb_controls
     */
    public function setNb_controls($nb_controls)
    {
        $this->nb_controls = $nb_controls;
    }

    /**
     * @param mixed $date_controle
     */
    public function setDate_controle($date_controle)
    {
        $this->date_controle = $date_controle;
    }

    /**
     * @param mixed $etat
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    }

    /**
     * @param mixed $date_publication
     */
    public function setDate_publication($date_publication)
    {
        $this->date_publication = $date_publication;
    }

    /**
     * @param mixed $commune_soumise
     */
    public function setCommune_soumise($commune_soumise)
    {
        $this->commune_soumise = $commune_soumise;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }

  
}

