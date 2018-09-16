<?php
namespace spamtonprof\getresponse_api;

use PDO;

class CampaignManager

{

    const eleve_en_essai = "eleve_en_essai", eleve_client = "eleve_client", parent_en_essai = "parent_en_essai", parent_client = "parent_client", fr_eleve_essai = "fr_eleve_essai", fr_parent_essai = "fr_parent_essai", elisabeth_migne_parent_client = "elisabeth_migne_parent_client", elisabeth_migne_eleve_client = "elisabeth_migne_eleve_client", 
    etudiant_en_essai = "etudiant_en_essai", etudiant_client = "etudiant_client", 
    fr_parent_essai_2 = "fr_parent_essai_2", parent_en_essai_2 = "parent_en_essai_2", 
    parent_client_2 = "parent_client_2", elisabeth_migne_parent_client_2 = "elisabeth_migne_parent_client_2", 
    parent_avec_reservation = "parent_avec_reservation", eleve_message_gratuit = "eleve_message_gratuit", interet_pour_essai = "interet_pour_essai", 
    parent_desins_essai = "parent_desins_essai", eleve_desins_essai = "eleve_desins_essai", etudiant_desins_essai = "etudiant_desins_essai", parent_desins_soutien = "parent_desins_soutien", eleve_desins_soutien = "eleve_desins_soutien", etudiant_desins_soutien = "etudiant_desins_soutien", 
    proposer_star_en_maths = "proposer_star_en_maths", 
    autre_solution_eleve = "autre_solution_eleve", autre_solution_etudiant = "autre_solution_etudiant", autre_solution_parent = "autre_solution_parent", 
    campagne_defaut = "campagne_defaut", parent_desins_soutien_2 = "parent_desins_soutien_2", autre_solution_parent_2 = "autre_solution_parent_2" , parent_desins_essai_2 = "parent_desins_essai_2",
    relance = "relance", suivi_hebdo = "suivi_hebdo", 
    remove_me = "remove_me";

    const desinscription = array(
        CampaignManager::parent_desins_essai,
        CampaignManager::eleve_desins_essai,
        CampaignManager::etudiant_desins_essai,
        CampaignManager::parent_desins_soutien,
        CampaignManager::eleve_desins_soutien,
        CampaignManager::etudiant_desins_soutien,
        CampaignManager::parent_desins_soutien_2,
        CampaignManager::parent_desins_essai_2
    );

    const autre_solution = array(
        CampaignManager::autre_solution_eleve,
        CampaignManager::autre_solution_etudiant,
        CampaignManager::autre_solution_parent,
        CampaignManager::autre_solution_parent_2,
        CampaignManager::proposer_star_en_maths
    );

    const markets = array(
        CampaignManager::parent_avec_reservation,
        CampaignManager::eleve_message_gratuit,
        CampaignManager::interet_pour_essai
    );

    const essai_eleve = array(
        CampaignManager::eleve_en_essai,
        CampaignManager::fr_eleve_essai
    );

    const inscrit_eleve = array(
        CampaignManager::eleve_client,
        CampaignManager::elisabeth_migne_eleve_client
    
    );

    const essai_parent = array(
        CampaignManager::fr_parent_essai,
        CampaignManager::parent_en_essai
    );

    const essai_parent_2 = array(
        CampaignManager::fr_parent_essai_2,
        CampaignManager::parent_en_essai_2
    );

    const inscrit_parent = array(
        CampaignManager::parent_client,
        CampaignManager::elisabeth_migne_parent_client
    );

    const inscrit_parent_2 = array(
        CampaignManager::parent_client_2,
        CampaignManager::elisabeth_migne_parent_client_2
    );

    private $getresponse;

    public function __construct()
    
    {
        $this->getresponse = new \GetResponse(GR_API);
    }

    /**
     *
     * @param string $name
     *            nom de la campagne
     * @return campaign | boolean retourne la première campagne correspondant au nom donné
     */
    public function get($name)
    
    {
        $params = array(
            "query" => array(
                "name" => $name
            )
        );
        
        $campaigns = $this->getresponse->searchCampaigns($params);
        
        foreach ($campaigns as $campaign) {
            
            $campaign = $this->getresponse->getCampaign($campaign->campaignId);
            
            $campaign = new \spamtonprof\getresponse_api\Campaign($campaign);
            
            return ($campaign);
        }
        
        return (false);
    }

    /**
     *
     * @param array $names
     * @return \spamtonprof\getresponse_api\Campaign|boolean
     */
    public function getList(array $names)
    
    {
        $campaigns = $this->getresponse->getCampaigns();
        
        $campaigns = (array) $campaigns;
        
        $i = 0;
        foreach ($campaigns as $campaign) {
            
            $campaign = new \spamtonprof\getresponse_api\Campaign($campaign);
            if (in_array($campaign->getName(), $names)) {
                $campaigns[$i];
            } else {
                unset($campaigns[$i]);
            }
            $i ++;
        }
        return ($campaigns);
    }

    /**
     *
     * @param string $campaignSource
     *            nom de la campagne source qui sera copié
     * @param string $campaignCible
     *            nom de la campagne cible qui sera créé
     */
    public function duplicateCampaign(string $nomCampaignSource, string $nomCampaignCible)
    {
        $campaignCible = $this->get($nomCampaignSource);
        
        unset($campaignCible->campaignId);
        
        unset($campaignCible->confirmation->subscriptionConfirmationBodyId);
        
        unset($campaignCible->confirmation->subscriptionConfirmationSubjectId);
        
        unset($campaignCible->profile);
        
        // prettyPrint($campaignCible);
        
        $campaignCible->name = $nomCampaignCible;
        
        $campaignCible = $this->getresponse->setCampaigns($campaignCible);
        
        return ($campaignCible);
    }
    /**
     * 
     * @param array $campaigns
     * @return array liste des id des campagnes
     */
    
    public function getIds(array $campaigns){
        
        $campaignsIds = [];
        
        foreach ($campaigns as $campaign){
            
            
            $campaignsIds[] = $campaign->campaignId;
            
        }
        
        return($campaignsIds);
            
        
    }
}