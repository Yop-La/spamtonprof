<?php
namespace spamtonprof\stp_api;

class Personne

{

    protected $adresse_mail, 
    $prenom, 
    $nom, 
    $telephone, 
    $type;
    public function __construct(array $donnees)
    
    {
        $this->hydrate($donnees);
        
        $this->type = strtolower(static::class);
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

    // public function estEndormi()
    
    // {
    
    // return $this->timeEndormi > time();
    
    // }
    
    // public function frapper(Personnage $perso)
    
    // {
    
    // if ($perso->id == $this->id)
    
    // {
    
    // return self::CEST_MOI;
    
    // }
    
    // if ($this->estEndormi())
    
    // {
    
    // return self::PERSO_ENDORMI;
    
    // }
    
    // // On indique au personnage qu'il doit recevoir des dégâts.
    
    // // Puis on retourne la valeur renvoyée par la méthode : self::PERSONNAGE_TUE ou self::PERSONNAGE_FRAPPE.
    
    // return $perso->recevoirDegats();
    
    // }
    
    // public function nomValide()
    
    // {
    
    // return !empty($this->nom);
    
    // }
    
    // public function recevoirDegats()
    
    // {
    
    // $this->degats += 5;
    
    // // Si on a 100 de dégâts ou plus, on supprime le personnage de la BDD.
    
    // if ($this->degats >= 100)
    
    // {
    
    // return self::PERSONNAGE_TUE;
    
    // }
    
    // // Sinon, on se contente de mettre à jour les dégâts du personnage.
    
    // return self::PERSONNAGE_FRAPPE;
    
    // }
    
    // public function reveil()
    
    // {
    
    // $secondes = $this->timeEndormi;
    
    // $secondes -= time();
    
    // $heures = floor($secondes / 3600);
    
    // $secondes -= $heures * 3600;
    
    // $minutes = floor($secondes / 60);
    
    // $secondes -= $minutes * 60;
    
    // $heures .= $heures <= 1 ? ' heure' : ' heures';
    
    // $minutes .= $minutes <= 1 ? ' minute' : ' minutes';
    
    // $secondes .= $secondes <= 1 ? ' seconde' : ' secondes';
    
    // return $heures . ', ' . $minutes . ' et ' . $secondes;
    
    // }
    public function adresse_mail()
    
    {
        return $this->adresse_mail;
    }

    public function prenom()
    
    {
        return $this->prenom;
    }

    public function nom()
    
    {
        return $this->nom;
    }

    public function type()
    
    {
        return $this->type;
    }

    public function setAdresse_mail($adresse_mail)
    
    {
        $this->adresse_mail = $adresse_mail;
    }

    public function setPrenom($prenom)
    
    {
        $this->prenom = $prenom;
    }

    public function setNom($nom)
    
    {
        $this->nom = $nom;
    }

    public function setType($type)
    
    {
        $this->type = type;
    }

    /**
     *
     * @return mixed
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     *
     * @param mixed $telephone
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }
}