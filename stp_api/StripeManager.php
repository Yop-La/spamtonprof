<?php
namespace spamtonprof\stp_api;

use PDO;
use DateTime;
use Exception;
use PayPal\Api\Plan;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Currency;

/*
 *
 * Cette classe sert à gérér ( CRUD ) les plans de paiement stripe
 *
 * Elle sert aussi à créer des abonnements, des clients, des paiements, etc
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
class StripeManager

{

    private $testMode = true;

    public function __construct($testMode = true)
    
    {
        if (gettype($testMode) == "string") {
            
            $testMode = ($testMode === 'true');
        }
        
        $this->testMode = $testMode;
    }

    public function addConnectSubscription($emailClient, $source, $refCompte, $planStripeId, $stripeProfId)
    {
        $slack = new \spamtonprof\slack\Slack();
        
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        try {
            
            $customer = \Stripe\Customer::create(array(
                
                'email' => $emailClient,
                
                'source' => $source,
                
                "metadata" => array(
                    
                    "ref_compte" => $refCompte
                
                )
            
            ));
            
            $subscription = \Stripe\Subscription::create(array(
                
                "customer" => $customer->id,
                
                "items" => array(
                    
                    array(
                        
                        "plan" => $planStripeId
                    
                    )
                
                ),
                
                "metadata" => array(
                    
                    "stripe_prof_id" => $stripeProfId
                
                )
            
            ));
            
            $slack->sendMessages("abonnement", array(
                
                "Nouvel abonnement, bien joué la team !!",
                
                "ref compte : " . $refCompte,
                
                "email client : " . $emailClient,
                
                "Ref abonnement stripe : " . $subscription->id
            
            ));
            
            return (true);
        } catch (Exception $e) {
            
            $slack->sendMessages("abonnement", array(
                
                "Oops un paiement pour abonnement vient d'échouer",
                
                "ref compte : " . $refCompte,
                
                "email client : " . $emailClient,
                
                "Faut voir ça avec le client",
                
                "Erreur : " . $e->getMessage()
            
            ));
            
            return (false);
        }
    }

    public function createSubscription($emailParent, $source, $refCompte, $planStripe)
    
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        try {
            
            $customer = \Stripe\Customer::create(array(
                
                'email' => $emailParent,
                
                'source' => $source,
                
                "metadata" => array(
                    
                    "compte" => $refCompte
                
                )
            
            ));
            
            $subscription = \Stripe\Subscription::create(array(
                
                "customer" => $customer->id,
                
                "items" => array(
                    
                    array(
                        
                        "plan" => $planStripe
                    
                    )
                
                )
            
            ));
            
            to_log_abonnement(array(
                
                "str1" => "ref compte : " . $refCompte,
                
                "str2" => "emailParent : " . $emailParent,
                
                "str3" => "ref abonnement stripe : " . $subscription->id
            
            ));
            
            return ($subscription);
        } catch (Exception $e) {
            
            to_log_slack(array(
                
                "str1" => "error paiement apres essai" . $e->getMessage(),
                
                "str2" => $emailParent
            
            ));
            
            return (null);
        }
    }

    public function getPublicStripeKey()
    
    {
        if ($this->testMode) {
            
            return TEST_PUBLIC_KEY_STRP;
        } else {
            
            return PROD_PUBLIC_KEY_STRP;
        }
    }

    public function getSecretStripeKey()
    
    {
        if ($this->testMode) {
            
            return TEST_SECRET_KEY_STRP;
        } else {
            
            return PROD_SECRET_KEY_STRP;
        }
    }

    public function resetStripePlans()
    
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey()); // ------------ test key ----------
                                                                
        // /*********** suprrimer tous les produits et tous les plans ********** //
        
        $plans = \Stripe\Plan::all(array(
            
            "limit" => 100
        
        ));
        
        $plans = $plans->data;
        
        foreach ($plans as $plan) {
            
            $plan->delete();
        }
        
        ;
        
        $allProducts = \Stripe\Product::all(array(
            
            "limit" => 10000
        
        ));
        
        $products = $allProducts->data;
        
        foreach ($products as $product) {
            
            $product->delete();
        }
        
        ;
        
        /**
         * *********** à éxecuter pour mise en prod stripe - sert à ajouter les produits à stripe et enregistrer les reds dans la bdd ***************************
         */
        
        $formuleManager = new FormuleManager();
        
        $formules = $formuleManager->getAll();
        
        foreach ($formules as $formule) {
            
            $product = \Stripe\Product::create(array(
                
                "name" => $formule->formule(),
                
                "type" => "service"
            
            ));
            
            echo ($product->id);
            
            if ($this->testMode) {
                
                $formule->setId_stripe_test($product->id);
            } else {
                
                $formule->setId_stripe($product->id);
            }
            
            $formuleManager->update($formule);
        }
        
        /**
         * *********** fin à éxecuter pour mise en prod stripe - sert à ajouter les produits à stripe et enregistrer les reds dans la bdd ***************************
         */
        
        // /************* deb : à éxecuter pour mise en prod stripe - sert à ajouter les plan de paiements à stripe et enregistrer les refs dans la bdd ****************************/
        
        $planPaiementManager = new PlanPaiementManager();
        
        $planPaiements = $planPaiementManager->getAll();
        
        // parcourir tous les plans de paiements en base
        
        foreach ($planPaiements as $planPaiement) {
            
            $product = $this->testMode ? $planPaiement->formule()->id_stripe_test() : $planPaiement->formule()->id_stripe();
            
            $plan = \Stripe\Plan::create(array(
                
                "amount" => $planPaiement->tarif() * 100,
                
                "interval" => "week",
                
                "product" => $product,
                
                "currency" => "eur",
                
                "nickname" => $planPaiement->nom_plan()
            
            ));
            
            if ($this->testMode) {
                
                $planPaiement->setRef_plan_stripe_test($plan->id);
            } else {
                
                $planPaiement->setRef_plan_stripe($plan->id);
            }
            
            $planPaiementManager->update($planPaiement);
        }
    }

    public function getAllSusbscriptions()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $subscriptions = \Stripe\Subscription::all(array(
            
            "limit" => 500
        
        ));
        
        return ($subscriptions);
    }

    public function createCustomAccount($tokenId, $pays)
    {
        
        // faire la création du compte stripe
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        try {
            
            $acct = \Stripe\Account::create(array(
                "country" => $pays,
                "type" => "custom",
                "account_token" => $tokenId
            ));
            return ($acct->id);
        } catch (\Exception $e) {
            
            $slack = new \spamtonprof\slack\Slack();
            $slack->sendMessages("onboarding-prof", array(
                $e->getMessage()
            ));
            
            return (false);
        }
    }

    public function updateCustomAccount($tokenId, $accoundId)
    {
        
        // faire la création du compte stripe
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        try {
            
            $acct = \Stripe\Account::retrieve($accoundId);
            $acct->account_token = $tokenId;
            $acct->save();
            
            return ($acct->id);
        } catch (\Exception $e) {
            
            return (false);
        }
    }

    public function addExternalAccount($tokenId, $accoundId)
    {
        
        // faire la création du compte stripe
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        try {
            
            $account = \Stripe\Account::retrieve($accoundId);
            $account->external_accounts->create(array(
                "external_account" => $tokenId
            ));
            return ($account->id);
        } catch (\Exception $e) {
            
            $slack = new \spamtonprof\slack\Slack();
            $slack->sendMessages("log", array(
                $e->getMessage()
            ));
            
            return (false);
        }
    }

    public function deleteAllProductsAndPlans()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $plans = \Stripe\Plan::all(array(
            
            "limit" => 100
        
        ));
        
        $plans = $plans->data;
        
        foreach ($plans as $plan) {
            
            $plan->delete();
        }
        
        ;
        
        $allProducts = \Stripe\Product::all(array(
            
            "limit" => 10000
        
        ));
        
        $products = $allProducts->data;
        
        foreach ($products as $product) {
            
            $product->delete();
        }
    }

    // pour créer tous les produits et les plans définis dans la base stp
    public function createProductsAndPlans()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
        $planMg = new \spamtonprof\stp_api\StpPlanManager();
        
        $constructor = array(
            "construct" => array(
                'plans'
            )
        );
        
        $formules = $formuleMg->getAll($constructor);
        
        foreach ($formules as $formule) {
            
            $strProduct = \Stripe\Product::create(array(
                "name" => "New " . $formule->getFormule(),
                "type" => "service"
            ));
            
            if ($this->testMode) {
                $formule->setRef_product_stripe_test($strProduct->id);
                $formuleMg->updateRefProductStripeTest($formule);
            } else {
                $formule->setRef_product_stripe($strProduct->id);
                $formuleMg->updateRefProductStripe($formule);
            }
            
            // créer la formule dans stripe
            $plans = $formule->getPlans();
            foreach ($plans as $plan) {
                
                $plan = \spamtonprof\stp_api\StpPlan::cast($plan);
                
                $strPlan = \Stripe\Plan::create(array(
                    "amount" => $plan->getTarif() * 100,
                    "interval" => "week",
                    "product" => $strProduct->id,
                    "currency" => "eur"
                ));
                
                if ($this->testMode) {
                    $plan->setRef_plan_stripe_test($strPlan->id);
                    $planMg->updateRefPlanStripeTest($plan);
                } else {
                    $plan->setRef_plan_stripe($strPlan->id);
                    $planMg->updateRefPlanStripe($plan);
                }
            }
        }
    }
}
