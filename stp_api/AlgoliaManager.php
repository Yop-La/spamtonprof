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

    /**
     *
     * @return \AlgoliaSearch\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *
     * @param \AlgoliaSearch\Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
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

    public function resetFormuleIndex()
    {
        $index = $this->client->initIndex('formule');

        $index->clearIndex();

        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();

        $constructor = array(
            "construct" => array(
                'defaultPlan'
            )
        );

        $formules = $formuleMg->getAll($constructor);

        $index->addObjects($formules);
    }

    public function resetReportingLbc()
    {
        $index = $this->client->initIndex('reportingLbc');

        $index->clearIndex();

        $compteLbcMg = new \spamtonprof\stp_api\LbcAccountManager();

        $comptes = $compteLbcMg->getAll("forReportingLbcIndex");

        $comptes = json_decode(json_encode($comptes), true);

        for ($i = 0; $i < count($comptes); $i ++) {

            $compte = $comptes[$i];

            $comptes[$i] = array_filter($compte, function ($v, $k) {
                return (! is_null($v));
            }, ARRAY_FILTER_USE_BOTH);
        }

        $index->addObjects($comptes);
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

    public function updateReportingLbc($info = false)
    {
        $index = $this->client->initIndex('reportingLbc');

        $compteLbcMg = new \spamtonprof\stp_api\LbcAccountManager();
        $comptes = [];
        if (! $info) {

            $comptes = $compteLbcMg->getAll("lastTwentyForReportingLbcIndex");
        } else if (is_array($info)) {

            $comptes = $compteLbcMg->getAll(array(
                "refComptes" => $info
            ));
        }

        $comptes = json_decode(json_encode($comptes), true);

        for ($i = 0; $i < count($comptes); $i ++) {

            $compte = $comptes[$i];

            $comptes[$i] = array_filter($compte, function ($v, $k) {
                return (! is_null($v));
            }, ARRAY_FILTER_USE_BOTH);
        }

        $index->saveObjects($comptes);
    }

    /* ------------------        début index abonnements           --------------  */
    
    public function resetAbonnement()
    {

        // ajout à l'index
        $index = $this->client->initIndex('abonnement');
        $index->clearIndex();

        $now = new \DateTime("2018-09-01");
        \Stripe\Stripe::setApiKey(PROD_SECRET_KEY_STRP);

        $subs = \Stripe\Subscription::all(array(
            'limit' => 5000,
            "created" => array(
                "gte" => $now->getTimestamp()
            )
        ));

        $subs = $subs->data;


        $abos = [];

        foreach ($subs as $sub) {

            $abo = new \spamtonprof\stripe\Subscription($sub);

            $abo ->toAlgoliaFormat();
            
            $abos[] = $abo;
        }

        $index->addObjects($abos);
    }

    public function addAbo($abo)
    {
        $index = $this->client->initIndex('abonnement');
        $index->addObject($abo);
    }

    public function updateAbo($abo)
    {
        $index = $this->client->initIndex('abonnement');
        $abo = json_decode(json_encode($abo), true);
        $index->saveObject($abo);
    }
    
    /* ------------------        fin index abonnements           --------------  */
    
    
    /* ------------------        début index transferts           --------------  */
    
    public function resetTransfert()
    {
        
        // ajout à l'index
        $index = $this->client->initIndex('transfert');
        $index->clearIndex();
        
        $now = new \DateTime("2018-09-01");
        \Stripe\Stripe::setApiKey(PROD_SECRET_KEY_STRP);
        
        $subs = \Stripe\Subscription::all(array(
            'limit' => 5000,
            "created" => array(
                "gte" => $now->getTimestamp()
            )
        ));
        
        $subs = $subs->data;
        
        
        $abos = [];
        
        foreach ($subs as $sub) {
            
            $abo = new \spamtonprof\stripe\Subscription($sub);
            
            $abo ->toAlgoliaFormat();
            
            $abos[] = $abo;
        }
        
        $index->addObjects($abos);
    }
}
