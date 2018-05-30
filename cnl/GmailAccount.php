<?php
namespace spamtonprof\cnl;

/**
 *
 * @author alexg
 *         pour cnl - création de compte google
 */
class GmailAccount implements \JsonSerializable

{

    protected $ref_compte_gmail, $prenom, $nom, $date_naissance, $adresse_mail, $cree, $password, $jourNaissance, $anneeNaissance, $moisNaissance;

    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
    }

    public function hydrate(array $donnees)
    
    {
        foreach ($donnees as $key => $value) 
        {
            
            $method = 'set' . ucfirst($key);
            
            if (method_exists($this, $method)) 
            {
                
                $this->$method($value);
            }
        }
    }

    /**
     *
     * @return mixed
     */
    public function getRef_compte_gmail()
    {
        return $this->ref_compte_gmail;
    }

    /**
     *
     * @return mixed
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     *
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     *
     * @return mixed
     */
    public function getDate_naissance()
    {
        return $this->date_naissance->format("PG_DATETIME_FORMAT");
        
    }

    /**
     *
     * @return mixed
     */
    public function getAdresse_mail()
    {
        return $this->adresse_mail;
    }

    /**
     *
     * @return mixed
     */
    public function getCree()
    {
        return $this->cree;
    }

    /**
     *
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @param mixed $ref_compte_gmail
     */
    public function setRef_compte_gmail($ref_compte_gmail)
    {
        $this->ref_compte_gmail = $ref_compte_gmail;
    }

    /**
     *
     * @param mixed $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     *
     * @param mixed $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     *
     * @param mixed $date_naissance
     */
    public function setDate_naissance($date_naissance)
    {
        
        
        $this->date_naissance = new \DateTime($date_naissance);
        $this->setMoisNaissance();
        $this->setAnneeNaissance();
        $this->setJourNaissance();
    }

    /**
     *
     * @param mixed $adresse_mail
     */
    public function setAdresse_mail($adresse_mail)
    {
        $this->adresse_mail = $adresse_mail;
    }

    /**
     *
     * @param mixed $cree
     */
    public function setCree($cree)
    {
        $this->cree = $cree;
    }

    /**
     *
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
    
    

    public function jsonSerialize()
    
    {
        $vars = get_object_vars($this);
        
        return $vars;
    }
    /**
     * @return mixed
     */
    public function getJourNaissance()
    {
        return $this->jourNaissance;
    }

    /**
     * @return mixed
     */
    public function getAnneeNaissance()
    {
        return $this->anneeNaissance;
    }

    /**
     * @return mixed
     */
    public function getMoisNaissance()
    {
        return $this->moisNaissance;
    }

    /**
     * @param mixed $jourNaissance
     */
    public function setJourNaissance()
    {
        $this->jourNaissance = $this->date_naissance->format("j");
    }

    /**
     * @param mixed $anneeNaissance
     */
    public function setAnneeNaissance()
    {
        $this->anneeNaissance = $this->date_naissance->format("Y");
    }

    /**
     * @param mixed $moisNaissance
     */
    public function setMoisNaissance()
    {
        $this->moisNaissance = $this->date_naissance->format("M");
    }

}