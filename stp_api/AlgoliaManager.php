<?php
namespace spamtonprof\stp_api;

/*
 *
 * Cette permet de communiquer avec aloglia
 *
 *
 */
class AlgoliaManager

{

    private $client;

    public function __construct()
    
    {
        $this->client = new \AlgoliaSearch\Client('3VXJH73YCI', ALGOLIA_SECRET);
    }

    public function resetSupportClientIndex()
    {
        $index = $this->client->initIndex('support_client');
        
        $index->clearIndex();
        
        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        
        $abonnements = $abonnementMg->getAll("all", array(
            "construct" => array(
                'ref_eleve',
                'ref_formule',
                'ref_parent',
                'ref_plan',
                'remarquesMatieres',
                'ref_statut_abonnement',
                'ref_prof'
            ),
            "ref_eleve" => array(
                "construct" => array(
                    'ref_classe',
                    'ref_profil'
                )
            ),
            "remarquesMatieres" => array(
                "construct" => array(
                    'ref_matiere'
                )
            )
        ));
        
        $index->addObjects($abonnements);
    }

    public function addAbonnement($refAbo)
    {
        $index = $this->client->initIndex('support_client');
        
        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        
        $constructor = array(
            "construct" => array(
                'ref_eleve',
                'ref_formule',
                'ref_parent',
                'ref_plan',
                'remarquesMatieres',
                'ref_statut_abonnement',
                'ref_prof'
            ),
            "ref_eleve" => array(
                "construct" => array(
                    'ref_classe',
                    'ref_profil'
                )
            ),
            "remarquesMatieres" => array(
                "construct" => array(
                    'ref_matiere'
                )
            )
        );
        
        $abonnement = $abonnementMg->get(array(
            'ref_abonnement' => $refAbo
        ), $constructor);
        
        $index->addObject($abonnement);
    }

    public function updateAbonnement($refAbo, $constructor = false)
    {
        $index = $this->client->initIndex('support_client');
        
        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        
        $abonnement = $abonnementMg->get(array(
            'ref_abonnement' => $refAbo
        ), $constructor);
        
        $abonnement = array_filter(json_decode(json_encode($abonnement), true), 'isNotNull');
        
        $index->partialUpdateObject($abonnement);
    }
}
