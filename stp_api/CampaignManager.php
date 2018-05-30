<?php

namespace spamtonprof\stp_api;



use PDO;



class CampaignManager

{
    
    const eleve_en_essai = "eleve_en_essai",
    eleve_client = "eleve_client",
    parent_en_essai = "parent_en_essai",
    parent_client = "parent_client",
    fr_eleve_essai = "fr_eleve_essai",
    fr_parent_essai = "fr_parent_essai",
    elisabeth_migne_parent_client = "elisabeth_migne_parent_client",
    elisabeth_migne_eleve_client = "elisabeth_migne_eleve_client",
    
    etudiant_en_essai = "etudiant_en_essai",
    etudiant_client = "etudiant_client",
    
    fr_parent_essai_2 = "fr_parent_essai_2",
    parent_en_essai_2 = "parent_en_essai_2",
    
    parent_client_2	= "parent_client_2",
    elisabeth_migne_parent_client_2 = "elisabeth_migne_parent_client_2", 
    
    
    parent_avec_reservation = "parent_avec_reservation",
    eleve_message_gratuit = "eleve_message_gratuit",
    interet_pour_essai = "interet_pour_essai",
    
    parent_desins_essai = "parent_desins_essai",
    eleve_desins_essai = "eleve_desins_essai",
    etudiant_desins_essai= "etudiant_desins_essai",
    parent_desins_soutien = "parent_desins_soutien",
    eleve_desins_soutien = "eleve_desins_soutien",
    etudiant_desins_soutien = "etudiant_desins_soutien",
    
    proposer_star_en_maths = "proposer_star_en_maths",
    
    autre_solution_eleve ="autre_solution_eleve",
    autre_solution_etudiant ="autre_solution_etudiant",
    autre_solution_parent = "autre_solution_parent",
    
    campagne_defaut = "campagne_defaut",
    
    relance = "relance",
    suivi_hebdo = "suivi_hebdo",
    
    remove_me = "remove_me";
    
    const array_desinscription = array(
        CampaignManager::parent_desins_essai,
        CampaignManager::eleve_desins_essai,
        CampaignManager::etudiant_desins_essai,
        CampaignManager::parent_desins_soutien,
        CampaignManager::eleve_desins_soutien,
        CampaignManager::etudiant_desins_soutien,
    );
    
    const array_autre_solution = array(
        CampaignManager::autre_solution_eleve,
        CampaignManager::autre_solution_etudiant,
        CampaignManager::autre_solution_parent,
        CampaignManager::proposer_star_en_maths
    );
    
    const array_markets = array(
        CampaignManager::parent_avec_reservation,
        CampaignManager::eleve_message_gratuit,
        CampaignManager::interet_pour_essai
    );
    
    const array_essai_eleve = array (
        CampaignManager::eleve_en_essai ,
        CampaignManager::fr_eleve_essai  
    ) ;
    
    const array_inscrit_eleve = array (
        CampaignManager::eleve_client ,
        CampaignManager::elisabeth_migne_eleve_client 
        
    ) ;
    
    const array_essai_parent = array (
        CampaignManager::fr_parent_essai ,
        CampaignManager::parent_en_essai 
    );

    const array_essai_parent_2 = array (
        CampaignManager::fr_parent_essai_2 ,
        CampaignManager::parent_en_essai_2 
    );
    
    
    
    const inscrit_parent = array (
        CampaignManager::parent_client ,
        CampaignManager::elisabeth_migne_parent_client 
    );
        
    const inscrit_parent_2 = array (
        CampaignManager::parent_client_2 ,
        CampaignManager::elisabeth_migne_parent_client_2 
    );
    
    
    private $_db;
    
    
    
    // Instance de PDO
    
    public function __construct()
    
    {
        
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
        
    }
    
    /**
     *
     * @param mixed $info : ref_campagne ou nom campagne
     * @return boolean|\spamtonprof\stp_api\Campaign
     */
    
    public function get($info)
    
    {
        
        if (is_int($info)) {
            
            $q = $this->_db->query('SELECT ref_campaign, ref_campaign_get_response, nom_campaign  FROM campaign WHERE ref_campaign = ' . $info);
            
            
            
            if ($q->rowCount() <= 0) {
                
                return (false);
                
            } else {
                
                return new Campaign($q->fetch(PDO::FETCH_ASSOC));
                
            }
            
        } elseif (is_string($info)) {
            
            
            
            $q = $this->_db->prepare('SELECT ref_campaign, ref_campaign_get_response, nom_campaign FROM campaign WHERE nom_campaign = :nom_campaign');
            
            $q->execute(array(
                
                "nom_campaign" => $info
                
            ));
            
            
            
            if ($q->rowCount() <= 0) {
                
                return (false);
                
            } else {
                
                return new Campaign($q->fetch(PDO::FETCH_ASSOC));
                
            }
            
        }
        
    }
    
    
    
    
    
}