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

    public function stopSubscription($subscriptionId)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        $subscription->cancel();
    }

    public function updateStripeProfId(string $subId, string $stripeProdId)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $sub = \Stripe\Subscription::retrieve($subId);

        $sub->metadata["stripe_prof_id"] = $stripeProdId;

        $sub->save();
    }

    public function transfertSubscriptionCharge($event_json, $subIdMan = false, $chargeIdMan = false)
    {
        serializeTemp($event_json);
        $slack = new \spamtonprof\slack\Slack();

        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $discount = false;

        $chargeId = $chargeIdMan;
        $subId = $subIdMan;
        if ($event_json) {
            $chargeId = $event_json->data->object->charge;
            $subId = $event_json->data->object->subscription;
            $discount = $event_json->data->object->discount;
        }

        $messages = [];

        $messages[] = "---------";
        $messages[] = "Event id : " . $event_json->id;
        $messages[] = "Nouveau paiement réussi";
        $messages[] = "chargeId : " . $chargeId;

        try {

            $sub = \Stripe\Subscription::retrieve($subId);

            if ($sub->metadata["stripe_prof_id"] != "") {

                $profId = $sub->metadata["stripe_prof_id"];

                $charge = \Stripe\Charge::retrieve($chargeId);

                $commission = 25;

                if ($discount) { // on change la commission

                    $percent_off = $discount->coupon->percent_off;

                    if ($percent_off >= 100) {
                        $slack->sendMessages('log', array(
                            "Event id : " . $event_json->id,
                            "Nouveau paiement réussi",
                            "chargeId : " . $chargeId,
                            'Promo de 100% : pas de transfert'
                        ));
                        exit(0);
                    }

                    if ($percent_off >= $commission) {
                        $commission = 0;
                    } else {

                        $commission = 100 * (1 - ((1 - $commission / 100) / (1 - $percent_off / 100)));
                    }
                }

                $part_prof = 1 - $commission / 100;

                $charge->transfer_group = $chargeId; // on utilise la charge id comme id de groupage de transactions
                $charge->save();

                // on transfère 75 % au prof
                \Stripe\Transfer::create(array(
                    "amount" => round($part_prof * $charge->amount),
                    "currency" => "eur",
                    "destination" => $profId,
                    "transfer_group" => $chargeId,
                    "source_transaction" => $chargeId
                ));

                $messages[] = "Transfert vers : " . $profId . " de " . round($part_prof * $charge->amount / 100) . "€ (".($part_prof*100)."%) réussi";
            } else {
                $messages[] = "Un abonnement vient d'être facturé sans compte prof associé";
            }
        } catch (\Exception $e) {

            $messages[] = $e->getMessage();
            return;
        } finally {
            $messages[] = $event_json;
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

    public function listActiveSubs(int $limit, $starting_after = false)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $params = [
            'limit' => $limit,
            'status' => 'active'
        ];

        if ($starting_after) {
            $params['starting_after'] = $starting_after;
        }

        $subs = \Stripe\Subscription::all($params);

        return ($subs);
    }

    public function addConnectSubscription($emailClient, $source, $refCompte, $planStripeId, $stripeProfId, $refAbonnement, \spamtonprof\stp_api\StpCompte $compte, $trialEnd = 'now', \spamtonprof\stp_api\StpCoupon $coupon = null)
    {
        $slack = new \spamtonprof\slack\Slack();

        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        try {

            $customer = null;
            if ($compte->getStripe_client()) {

                $customer = \Stripe\Customer::retrieve($compte->getStripe_client());

                if ($source) {
                    $customer->source = $source;
                    $customer->save();
                }
            } else {

                $customer = \Stripe\Customer::create(array(

                    'email' => $emailClient,

                    'source' => $source,

                    "metadata" => array(

                        "ref_compte" => $refCompte
                    )
                ));
            }

            $subParams = array(

                "customer" => $customer->id,

                "items" => array(

                    array(

                        "plan" => $planStripeId
                    )
                ),

                "trial_end" => $trialEnd,

                "metadata" => array(

                    "ref_compte" => $refCompte,
                    "ref_abonnement" => $refAbonnement,
                    "stripe_prof_id" => $stripeProfId
                )
            );

            if ($coupon) {
                if ($this->testMode) {
                    $subParams['coupon'] = $coupon->getRef_stripe_test();
                } else {
                    $subParams['coupon'] = $coupon->getRef_stripe();
                }
            }

            $subscription = \Stripe\Subscription::create($subParams);

            $slack->sendMessages("abonnement", array(

                "Nouvel abonnement, bien joué la team !!",

                "ref compte : " . $refCompte,

                "email client : " . $emailClient,

                "Ref abonnement stripe : " . $subscription->id
            ));

            return (array(
                "subId" => $subscription->id,
                "cusId" => $customer->id
            ));
        } catch (Exception $e) {

            $slack->sendMessages("abonnement", array(

                "Oops un paiement pour abonnement vient d'échouer",

                "ref compte : " . $refCompte,

                "email client : " . $emailClient,

                "Faut voir ça avec le client",

                "Erreur : " . $e->getMessage()
            ));
        }

        return (false);
    }

    public function updateSubscriptionPlan($subId, \spamtonprof\stp_api\StpPlan $plan)
    {
        $planId = $plan->getRef_plan_stripe();
        if ($this->testMode) {
            $planId = $plan->getRef_plan_stripe_test();
        }

        // mise à jour de l'abonnement stripe
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
            "amount" => round($amount * 100),
            "currency" => "eur",
            "destination" => $prof->getStripe_id(),
            "description" => $description
        ));

        return ($transfert);
    }

    /* pour mettre à jour la cb d'un compte stripe */
    public function updateCb($refCompte, $testMode, $token)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $compteMg = new \spamtonprof\stp_api\StpCompteManager();
        $compte = $compteMg->get(array(
            'ref_compte' => $refCompte
        ));

        if ($compte->getStripe_client()) {
            $customer = \Stripe\Customer::retrieve($compte->getStripe_client());
            $customer->source = $token;
            $customer->save();
            return (true);
        } else {
            return (false);
        }
    }

    // pour créer tous les produits et les plans définis dans la base stp qui ne sont pas dans stripe
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

        $formules = $formuleMg->getAll(array(
            'getFormuleNotInStripe' => $this->testMode
        ), $constructor);

        foreach ($formules as $formule) {

            $strProduct = \Stripe\Product::create(array(
                "name" => "From Tool : " . $formule->getFormule(),
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

    public function addTrial($subId, $endDay, $prorate)
    {
        $endDay = \DateTime::createFromFormat(PG_DATE_FORMAT, $endDay);

        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        \Stripe\Subscription::update($subId, [
            'trial_end' => $endDay->getTimestamp(),
            'prorate' => $prorate
        ]);
    }
}
