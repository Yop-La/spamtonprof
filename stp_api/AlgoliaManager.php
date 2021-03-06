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
        $slack = new \spamtonprof\slack\Slack();

        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        $offset = 0;

        do {

            $abonnements = $abonnementMg->getAll(array(
                'all',
                'offset' => $offset,
                'limit' => 10
            ), array(
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
                        'ref_niveau'
                    )
                ),
                "remarquesMatieres" => array(
                    "construct" => array(
                        'ref_matiere'
                    )
                )
            ));

            $offset = $offset + 10;

            $slack->sendMessages('log', array(
                'reset support client en cours. Offset : ' . $offset
            ));

            $index->addObjects($abonnements);
        } while (count($abonnements) != 0);
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

    public function resetMatiereIndex()
    {
        $index = $this->client->initIndex('matiere');

        $index->clearIndex();

        $matiereMg = new \spamtonprof\stp_api\StpMatiereManager();

        $matieres = $matiereMg->getAll(array(
            'all'
        ));

        $index->addObjects($matieres);
    }

    public function resetNiveauIndex()
    {
        $index = $this->client->initIndex('niveau');

        $index->clearIndex();

        $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();

        $niveaux = $niveauMg->getAll(array(
            'all'
        ));

        $index->addObjects($niveaux);
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
    
    public function addTransaction(\stdClass $transaction, $objectId){
        

        
        $index = $this->client->initIndex('stripe_transaction');
        
        
        $index->addObject($transaction,$objectId);
        
        
        
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
                    'ref_niveau'
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

    public function deleteAbo($objectID){

        $index = $this->client->initIndex('support_client');
        $index->deleteObject($objectID);
        
    }
    
    public function updateAbonnements($refAbos, $constructor = false)
    {
        $index = $this->client->initIndex('support_client');

        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();

        $abonnements = $abonnementMg->getAll(array(
            'ref_abonnements' => $refAbos
        ), $constructor);

        for ($i = 0; $i < count($abonnements); $i ++) {
            $abonnement = $abonnements[$i];
            $abonnement = array_filter(json_decode(json_encode($abonnement), true), 'isNotNull');
            $abonnements[$i] = $abonnement;
        }

        $index->partialUpdateObjects($abonnements);
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

    /* ------------------ d�but index abonnements -------------- */
    public function resetAbonnement()
    {

        // ajout � l'index
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

            $abo->toAlgoliaFormat();

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

    public function updateSupport($abo)
    {
        $index = $this->client->initIndex('support_client');
        $abo = json_decode(json_encode($abo), true);
        $index->saveObject($abo);
    }

    /* ------------------ fin index abonnements -------------- */

    /* ------------------ d�but index transferts -------------- */
    public function resetTransfert()
    {

        // ajout � l'index
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

            $abo->toAlgoliaFormat();

            $abos[] = $abo;
        }

        $index->addObjects($abos);
    }

    public function getAll($indexName)
    {
        $index = $this->client->initIndex($indexName);

        $hits = [];
        foreach ($index->browse('') as $hit) {
            $hits[] = $hit;
        }

        return ($hits);
    }

    public function resetNbMessage()
    {
        $index = $this->client->initIndex("support_client");
        $hits = $this->getAll("support_client");

        $hitIndex = 0;
        foreach ($hits as $hit) {
            $hit["nb_message"] = 0;
            $hits[$hitIndex] = $hit;
            $hitIndex ++;
        }

        $index->saveObjects($hits);
    }
}
