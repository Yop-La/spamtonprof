<?php
namespace spamtonprof\stp_api;

class stpProfManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpProf $stpProf)
    {
        $q = $this->_db->prepare('insert into stp_prof(email_perso, prenom, nom, telephone, onboarding_step, date_naissance, sexe) 
            values( :email_perso,:prenom,:nom,:telephone, :onboarding_step, :date_naissance, :sexe)');
        $q->bindValue(':email_perso', $stpProf->getEmail_perso());
        $q->bindValue(':prenom', $stpProf->getPrenom());
        $q->bindValue(':nom', $stpProf->getNom());
        $q->bindValue(':telephone', $stpProf->getTelephone());
        $q->bindValue(':onboarding_step', $stpProf->getOnboarding_step());
        $q->bindValue(':date_naissance', $stpProf->getDate_naissance()
            ->format(PG_DATE_FORMAT));
        $q->bindValue(':sexe', $stpProf->getSexe());
        $q->execute();
        
        $stpProf->setRef_prof($this->_db->lastInsertId());
        
        return ($stpProf);
    }

    public function get($info)
    {
        $q = null;
        
        if (array_key_exists('email_perso', $info)) {
            
            $emailPerso = $info['email_perso'];
            
            $q = $this->_db->prepare('select * from stp_prof where lower(email_perso) like lower(:email_perso)');
            
            $q->bindValue(':email_perso', $emailPerso);
        }
        
        if (array_key_exists('user_id_wp', $info)) {
            
            $userId = $info['user_id_wp'];
            
            $q = $this->_db->prepare('select * from stp_prof where lower(user_id_wp) like lower(:user_id_wp)');
            
            $q->bindValue(':user_id_wp', $userId);
        }
        
        if (array_key_exists('ref_prof', $info)) {
            
            $refProf = $info['ref_prof'];
            
            $q = $this->_db->prepare('select * from stp_prof where ref_prof = :ref_prof');
            
            $q->bindValue(':ref_prof', $refProf);
        }
        
        if (! is_null($q)) {
            
            $q->execute();
            $data = $q->fetch(\PDO::FETCH_ASSOC);
            
            if ($data) {
                return (new \spamtonprof\stp_api\stpProf($data));
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    public function getAll()
    {
        $profs = [];
        
        $q = $this->_db->prepare('select * from stp_prof ');
        
        $q->execute();
       
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            
            $profs[] = new \spamtonprof\stp_api\stpProf($data);
        }
        return($profs);
    }

    public function updateUserIdWp(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set user_id_wp = :user_id_wp where ref_prof = :ref_prof');
        
        $q->bindValue(':user_id_wp', $prof->getUser_id_wp());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }

    public function updateOnboarding(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set onboarding = :onboarding where ref_prof = :ref_prof');
        
        $q->bindValue(':onboarding', $prof->getOnboarding(), \PDO::PARAM_BOOL);
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }

    public function updateStripeId(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set stripe_id = :stripe_id where ref_prof = :ref_prof');
        
        $q->bindValue(':stripe_id', $prof->getStripe_id());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }
    
    public function updateStripeIdTest(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set stripe_id_test = :stripe_id_test where ref_prof = :ref_prof');
        
        $q->bindValue(':stripe_id_test', $prof->getStripe_id_test());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }
    
    public function updateAdresse(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set adresse = :adresse where ref_prof = :ref_prof');
        
        $q->bindValue(':adresse', $prof->getAdresse());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }
    

    public function updateVille(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set ville = :ville where ref_prof = :ref_prof');
        
        $q->bindValue(':ville', $prof->getVille());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }

    
    public function updateCodePostal(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set code_postal = :code_postal where ref_prof = :ref_prof');
        
        $q->bindValue(':code_postal', $prof->getCode_postal());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }

    
    public function updatePays(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set pays = :pays where ref_prof = :ref_prof');
        
        $q->bindValue(':pays', $prof->getPays());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }
    
    
    public function updateOnboarding_step(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set onboarding_step = :onboarding_step where ref_prof = :ref_prof');
        
        $q->bindValue(':onboarding_step', $prof->getOnboarding_step());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }

    public function updateIban(\spamtonprof\stp_api\stpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set iban = :iban where ref_prof = :ref_prof');
        
        $q->bindValue(':iban', $prof->getIban());
        
        $q->bindValue(':ref_prof', $prof->getRef_prof());
        
        $q->execute();
        
        return ($prof);
    }
    
    
    public function cast(\spamtonprof\stp_api\stpProf $object)
    {
        return ($object);
    }
}
