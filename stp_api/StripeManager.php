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
 * Cette classe sert � g�r�r ( CRUD ) les plans de paiement stripe
 *
 * Elle sert aussi � cr�er des abonnements, des clients, des paiements, etc
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

    public function stopSubscription($subscriptionId)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        $subscription->cancel();
    }

    public function transfertSubscriptionCharge($event_json, $subIdMan = false, $chargeIdMan = false)
    {
        $slack = new \spamtonprof\slack\Slack();

        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $chargeId = $chargeIdMan;
        $subId = $subIdMan;
        if ($event_json) {
            $chargeId = $event_json->data->object->charge;
            $subId = $event_json->data->object->subscription;
        }
   
        $messages = [];

        $messages[] = "---------";
        $messages[] = "Nouveau paiement r�ussi";
        $messages[] = "chargeId : " . $chargeId;

        try {

            $sub = \Stripe\Subscription::retrieve($subId);

            if ($sub->metadata["stripe_prof_id"] != "") {

                $profId = $sub->metadata["stripe_prof_id"];

                $charge = \Stripe\Charge::retrieve($chargeId);
                $charge->transfer_group = $chargeId; // on utilise la charge id comme id de groupage de transactions
                $charge->save();

                // on transf�re 75 % au prof
                $transfer = \Stripe\Transfer::create(array(
                    "amount" => round(0.75 * $charge->amount),
                    "currency" => "eur",
                    "destination" => $profId,
                    "transfer_group" => $chargeId,
                    "source_transaction" => $chargeId
                ));

                $messages[] = "Transfert vers : " . $profId . "r�ussi";
            } else {
                $messages[] = "Un abonnement vient d'�tre factur� sans compte prof associ�";
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
            return;
        } finally {
            $slack->sendMessages("stripe", $messages);
        }
    }

    public function __construct($testMode = true)

    {
        if (gettype($testMode) == "string") {

            $testMode = ($testMode === 'true');
        }

        $this->testMode = $testMode;
    }

    public function addConnectSubscription($emailClient, $source, $refCompte, $planStripeId, $stripeProfId, $refAbonnement, \spamtonprof\stp_api\StpCompte $compte)
    {
        $slack = new \spamtonprof\slack\Slack();

        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        try {

            $customer = null;
            if ($compte->getStripe_client()) {

                $customer = \Stripe\Customer::retrieve($compte->getStripe_client());
                $customer->source = $source;
                $customer->save();
            } else {

                $customer = \Stripe\Customer::create(array(

                    'email' => $emailClient,

                    'source' => $source,

                    "metadata" => array(

                        "ref_compte" => $refCompte
                    )
                ));
            }

            $subscription = \Stripe\Subscription::create(array(

                "customer" => $customer->id,

                "items" => array(

                    array(

                        "plan" => $planStripeId
                    )
                ),

                "metadata" => array(

                    "ref_compte" => $refCompte,
                    "ref_abonnement" => $refAbonnement,
                    "stripe_prof_id" => $stripeProfId
                )
            ));

            $slack->sendMessages("abonnement", array(

                "Nouvel abonnement, bien jou� la team !!",

                "ref compte : " . $refCompte,

                "email client : " . $emailClient,

                "Ref abonnement stripe : " . $subscription->id
            ));

            return (array(
                "subId" => $subscription->id,
                "cusId" => $customer->id
            ));
        } catch (Exception $e) {
            return (false);

            $slack->sendMessages("abonnement", array(

                "Oops un paiement pour abonnement vient d'�chouer",

                "ref compte : " . $refCompte,

                "email client : " . $emailClient,

                "Faut voir �a avec le client",

                "Erreur : " . $e->getMessage()
            ));
        }
    }

    public function updateSubscriptionPlan($subId, \spamtonprof\stp_api\StpPlan $plan)
    {
        $planId = $plan->getRef_plan_stripe();
        if ($this->testMode) {
            $planId = $plan->getRef_plan_stripe_test();
        }

        // mise � jour de l'abonnement stripe
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $sub = \Stripe\Subscription::retrieve($subId);

        \Stripe\Subscription::update($subId, [
            'cancel_at_period_end' => false,
            'items' => [
                [
                    'id' => $sub->items->data[0]->id,
                    'plan' => $planId
                ]
            ]
        ]);

        $sub->save();
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
         * *********** � �xecuter pour mise en prod stripe - sert � ajouter les produits � stripe et enregistrer les reds dans la bdd ***************************
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
         * *********** fin � �xecuter pour mise en prod stripe - sert � ajouter les produits � stripe et enregistrer les reds dans la bdd ***************************
         */

        // /************* deb : � �xecuter pour mise en prod stripe - sert � ajouter les plan de paiements � stripe et enregistrer les refs dans la bdd ****************************/

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

        // faire la cr�ation du compte stripe
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

        // faire la cr�ation du compte stripe
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

        // faire la cr�ation du compte stripe
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

        $subs = \Stripe\Subscription::all(array(
            'limit' => 100
        ));
        foreach ($subs as $sub) {

            $sub->cancel();
        }

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

    /* pour faire des transferts manuels vers le compte d'un prof */
    public function manualTransfert($emailProf, $amount, $description)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $profMg = new \spamtonprof\stp_api\StpProfManager();
        $prof = $profMg->get(array(
            'email_stp' => $emailProf
        ));

        $transfert = \Stripe\Transfer::create(array(
            "amount" => $amount * 100,
            "currency" => "eur",
            "destination" => $prof->getStripe_id(),
            "description" => $description
        ));

        return ($transfert);
    }

    // pour cr�er tous les produits et les plans d�finis dans la base stp
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

            // cr�er la formule dans stripe
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
