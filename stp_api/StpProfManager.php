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
        $q = $this->_db->prepare('insert into stp_prof(email_perso, prenom, nom, telephone) values( :email_perso,:prenom,:nom,:telephone)');
        $q->bindValue(':email_perso', $stpProf->getEmail_perso());
        $q->bindValue(':prenom', $stpProf->getPrenom());
        $q->bindValue(':nom', $stpProf->getNom());
        $q->bindValue(':telephone', $stpProf->getTelephone());
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
        
        $q->bindValue(':onboarding', $prof->getOnboarding(),\PDO::PARAM_BOOL);
        
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
}
