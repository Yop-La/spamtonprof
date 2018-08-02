<?php
namespace spamtonprof\stp_api;

class stpEleveManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(stpEleve $stpEleve)
    {
        $q = $this->_db->prepare('insert into stp_eleve(email, prenom, ref_classe, nom, telephone, same_email, ref_profil) values( :email,:prenom,:ref_classe,:nom,:telephone,  :same_email, :ref_profil)');
        $q->bindValue(':email', $stpEleve->getEmail());
        $q->bindValue(':prenom', $stpEleve->getPrenom());
        $q->bindValue(':ref_classe', $stpEleve->getRef_classe());
        $q->bindValue(':nom', $stpEleve->getNom());
        $q->bindValue(':telephone', $stpEleve->getTelephone());
        $q->bindValue(':same_email', $stpEleve->getSame_email(),\PDO::PARAM_BOOL);
        $q->bindValue(':ref_profil', $stpEleve->getRef_profil());
        
        $q->execute();
        $stpEleve->setRef_eleve($this->_db->lastInsertId());
        return ($stpEleve);
    }
    
    public function updateRefCompteWp(stpEleve $eleve)
    {
        $q = $this->_db->prepare('update stp_eleve set ref_compte_wp = :ref_compte_wp where ref_eleve = :ref_eleve');
        $q->bindValue(':ref_compte_wp', $eleve->getRef_compte_wp());
        $q->bindValue(':ref_eleve', $eleve->getRef_eleve());
        $q->execute();
        
        return ($eleve);
    }
    
    public function get($info){
        
        if(array_key_exists("email", $info)){
            
            $email = $info["email"];
            
            $q = $this->_db->prepare('select * from stp_eleve where lower(email) like lower(:email)');
            $q->bindValue(':email', $email);
            $q->execute();
            
            $data = $q->fetch(\PDO::FETCH_ASSOC);
            
            if($data){
                return(new \spamtonprof\stp_api\stpEleve($data));
            }else{
                return (false);
            }
            
        }
        
    }
}
