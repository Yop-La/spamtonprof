<?php
namespace spamtonprof\stp_api;

use DateTime;
use Exception;
use \PayPal\Api\Plan;

/*
 * Cette classe sert à gérér ( CRUD ) les plans de paiement paypal
 * attention un billing plan ( equivalent service/produit dans stripe ) ne peut avoir qu'un seul type de paiement definition regulier
 * ainsi ici un billing plan est équivalent à un plan dans stp
 *
 *
 *
 *
 *
 *
 */
class PaypalManager

{

    private $testMode = true, $bdd, $apiContext;

    public function __construct($testMode = true)
    
    {
        if (gettype($testMode) == "string") {
            $testMode = ($testMode === 'true');
        }
        $this->testMode = $testMode;
        $this->setApiContext();
        $bdd = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function createBilingAgreement($paypalPlanId)
    {
        $startDate = new Datetime();
        $startDate->add(new \DateInterval('P7D'));
        
        $planPaiementMg = new \spamtonprof\stp_api\PlanPaiementManager();
        
        $planStp;
        if ($this->testMode) {
            
            $planStp = $planPaiementMg->getWithRefPaypal(array(
                "ref_paypal_test" => $paypalPlanId
            ));
        } else {
            
            $planStp = $planPaiementMg->getWithRefPaypal(array(
                "ref_paypal_prod" => $paypalPlanId
            ));
        }
        

        
        // Create new agreement
        $agreement = new \PayPal\Api\Agreement();
        $agreement->setName($planStp->formule()
            ->formule())
            ->setDescription("Abonnement de " . $planStp->tarif() . "  	euros par semaine sans engagement - ".$planStp->formule()
                ->formule())
            ->setStartDate($startDate->format(Datetime::ISO8601));
        
        // Set plan id
        $plan = new \PayPal\Api\Plan();
        $plan->setId($paypalPlanId);
        $agreement->setPlan($plan);
        
        // Add payer type
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);
        
        try {
            // Create agreement
            $agreement = $agreement->create($this->apiContext);
            $links = $agreement->getLinks();
            foreach ($links as $link) {
                if ($link->getRel() == "approval_url") {
                    // on parse le lien pour récuper le token qui est un paramètre du lien
                    $parts = parse_url($link->getHref());
                    parse_str($parts['query'], $query);
                    $token = $query['token'];
                    return ($token);
                }
            }
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            echo $ex->getCode();
            echo $ex->getData();
            die($ex);
        } catch (Exception $ex) {
            die($ex);
        }
    }
    public function getAgreement($agreementId){                $agreement = new \PayPal\Api\Agreement();                $agreement= $agreement->get($agreementId,$this->apiContext);                return($agreement);            }        public function getAgreementTransactions($agreementId){                $params = array('start_date' => date('Y-m-d', strtotime('-15 years')), 'end_date' => date('Y-m-d', strtotime('+15 years')));                $result = \PayPal\Api\Agreement::searchTransactions($agreementId, $params, $this->apiContext);                        return($result);            }    
    public function executeBilingAgreement($paymentToken)
    {
        $agreement = new \PayPal\Api\Agreement();        
        
        try {
            
            $finalAgreement = $agreement->execute($paymentToken, $this->apiContext);
            return ($finalAgreement->id);
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            echo $ex->getCode();
            echo $ex->getData();
            return (null);
            die($ex);
        } catch (Exception $ex) {
            return (null);
            die($ex);
        }
    }

    public function resetPaypalPlans()
    {
        try {
            $this->deleteAllPaypalPlan();
            echo (" tous les plans sont supprimés" . '<br>' . '<br>' . '<br>');
            $this->createPaypalPlanInDb();
        } catch (Exception $e) {
            $e->getMessage();
        }
    }
 
    public function printPaypalPlan($planId)
    {
            $paypalPlan = \PayPal\Api\Plan::get($planId, $this->apiContext);
            echo($paypalPlan);

    }

    // ****** créer tous les plans de notre bdd ******/
    public function createPaypalPlanInDb()
    {
        $planPaiementManager = new PlanPaiementManager();
        $formuleManager = new FormuleManager();
        $plans = $planPaiementManager->getAll();
        foreach ($plans as $plan) {
            $formule = $plan->formule();
            
            // Create a new billing plan ( equivalent plan dans stp ici plan definition = plan stp . pas vraiment d'équivalent pour les formules)
            $paypalPlan = new \PayPal\Api\Plan();
            $paypalPlan->setName($formule->formule())
                ->setDescription($formule->formule())
                ->setType('INFINITE');
            
            // Set billing plan definitions
            $paymentDefinition = new \PayPal\Api\PaymentDefinition();
            $paymentDefinition->setName($plan->nom_plan())
                ->setType('REGULAR')
                ->setFrequency('WEEK')
                ->setFrequencyInterval('1')
                ->setCycles('0')
                ->setAmount(new \PayPal\Api\Currency(array(
                'value' => $plan->tarif(),
                'currency' => 'EUR'
            )));
            
            // Set merchant preferences
            $merchantPreferences = new \PayPal\Api\MerchantPreferences();
            $merchantPreferences->setReturnUrl(home_url('abonnement-apres-essai/'))
                ->setCancelUrl(home_url('contact/'))
                ->setAutoBillAmount('yes')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0')
                ->setSetupFee(new \PayPal\Api\Currency(array(
                'value' => $plan->tarif(),
                'currency' => 'EUR'
            )));
            
            $paypalPlan->setPaymentDefinitions(array(
                $paymentDefinition
            ));
            $paypalPlan->setMerchantPreferences($merchantPreferences);
            
            // create plan
            try {
                $createdPlan = $paypalPlan->create($this->apiContext);
                echo ($createdPlan . "<br>" . "<br>");
                try {
                    $patch = new \PayPal\Api\Patch();
                    $value = new \PayPal\Common\PayPalModel('{"state":"ACTIVE"}');
                    $patch->setOp('replace')
                        ->setPath('/')
                        ->setValue($value);
                    $patchRequest = new \PayPal\Api\PatchRequest();
                    $patchRequest->addPatch($patch);
                    $createdPlan->update($patchRequest, $this->apiContext);
                    
                    $paypalPlan = \PayPal\Api\Plan::get($createdPlan->getId(), $this->apiContext);
                    
                    // enregistrer les id paypal dans la base stp
                    if ($this->testMode) {
                        
                        $plan->setRef_paypal_test($paypalPlan->getId());
                        $planPaiementManager->update($plan);
                    } else {
                        
                        $plan->setRef_paypal_prod($paypalPlan->getId());
                        $planPaiementManager->update($plan);
                    }
                } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                    echo $ex->getCode();
                    echo $ex->getData();
                    die($ex);
                } catch (Exception $ex) {
                    die($ex);
                }
            } catch (\PayPal\Exception\PayPalConnectionException $ex) {
                echo $ex->getCode();
                echo $ex->getData();
                die($ex);
            } catch (Exception $ex) {
                die($ex);
            }
        }
    }

    // ****** fin créer tous les plans de notre bdd ******/
    
    /* **** pour supprimer les plans actifs **** */
    public function deleteAllPaypalPlan()
    {
        try {
            do {
                $params = array(
                    'page_size' => '20',
                    "status" => "ACTIVE",
                    "total_required" => "yes"
                );
                $planList = \PayPal\Api\Plan::all($params, $this->apiContext);
                $nbPlans = $planList->getTotalItems();
                if (is_null($nbPlans)) {
                    echo ("pas de plan ...");
                    break;
                }
                $plans = $planList->getPlans();
                foreach ($plans as $plan) {
                    echo ($plan . "<br>" . "<br>");
                    $plan->delete($this->apiContext);
                }
            } while (true);
        } catch (Exception $ex) {
            echo ($ex->getMessage());
            exit(1);
        }
    }

    /* **** fin pour supprimer 20 plans actifs **** */
    public function listAllPaypalPlan()
    {
        try {
            $indexPage = 0;
            do {
                $params = array(
                    'page_size' => '20',
                    "status" => "ACTIVE",
                    "total_required" => "yes",
                    "page" => $indexPage
                );
                $indexPage ++;
                $planList = \PayPal\Api\Plan::all($params, $this->apiContext);
                $nbPlans = $planList->getTotalItems();
                if (is_null($nbPlans)) {
                    echo ("pas de plan ...");
                    break;
                }
                $plans = $planList->getPlans();
                
                foreach ($plans as $plan) {
                    echo ($plan . "<br>");
                    foreach ($plan->getPaymentDefinitions() as $paymentDefinition) {
                        echo ($paymentDefinition . "<br>");
                    }
                }
                echo ("nb items courant " . count($plans) . "<br><br>");
                if (count($plans) < 20) {
                    break;
                }
            } while (true);
        } catch (Exception $ex) {
            echo ($ex->getMessage());
            exit(1);
        }
    }

    private function setApiContext()
    {
        $clientId = TEST_CLIENT_ID;
        $clientSecret = TEST_CLIENT_SECRET;
        if (! $this->testMode) {
            $clientId = PROD_CLIENT_ID;
            $clientSecret = PROD_CLIENT_SECRET;
        }
        $apiContext = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential($clientId, // ClientID
        $clientSecret)); // ClientSecret
        
        if (! $this->testMode) {
            $apiContext->setConfig(array(
                'mode' => 'live'
            ));
        }
        $this->apiContext = $apiContext;
    }
}
