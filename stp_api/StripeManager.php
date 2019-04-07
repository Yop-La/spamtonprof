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
 * Cette classe sert Ã  gÃ©rÃ©r ( CRUD ) les plans de paiement stripe
 *
 * Elle sert aussi Ã  crÃ©er des abonnements, des clients, des paiements, etc
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

    /*
     * utlisation de la function determine_com
     * $solde = 17.5; // en €
     * $commission = 20;
     * $charge_amt = 2250; // en centimes
     * $info = array("solde" => array("solde" => $solde, "com" => $commission, 'charge_amt' => $charge_amt));
     * determine_com($info)
     */
    private function determine_com($info)
    {
        if (array_key_exists("solde", $info)) {
            $info = $info['solde'];
            $solde = $info['solde'];
            $com = $info['com'] / 100;
            $charge_amt = $info['charge_amt'] / 100;
            
            $com_solde = 0;
            
            if (abs($solde) <= 0.005) {
                return ($com_solde);
            }
            
            $part_prof_before = (1 - $com) * $charge_amt;
            
            if ($solde >= $part_prof_before) {
                
                $com_solde = 1 - $com;
            } else {
                
                $com_solde = $solde / $charge_amt;
            }
            return (100 * $com_solde);
        }
    }

    public function transfertSubscriptionCharge($event_json, $chargeIdMan = false)
    {
        serializeTemp($event_json);
        $slack = new \spamtonprof\slack\Slack();
        
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $discount = false;
        
        $messages = [];
        $messages[] = "---------";
        
        $chargeId = $chargeIdMan;
        $subId = false;
        if ($event_json && ! $chargeIdMan) {
            $messages[] = "Event id : " . $event_json->id;
            $chargeId = $event_json->data->object->charge;
            $subId = $event_json->data->object->subscription;
            $discount = $event_json->data->object->discount;
            $amount_paid = $event_json->data->object->amount_paid;
            
            if ($amount_paid == 0) {
                $messages[] = "Facture d'un montant nul";
                $slack->sendMessages("stripe", $messages);
                return;
            }
        } else {
            $messages[] = "Transfert manuel";
        }
        
        $charge = \Stripe\Charge::retrieve($chargeId);
        $charge_amt = $charge->amount;
        $transfert_group = $charge->transfer_group;
        
        if (! $subId) {
            $invoice = $this->retrieve_invoice($charge->invoice);
            $subId = $invoice->subscription;
        }
        
        $messages[] = "Nouveau paiement réussi";
        $messages[] = "chargeId : " . $chargeId;
        
        try {
            
            $sub = \Stripe\Subscription::retrieve($subId);
            
            if ($sub->metadata["stripe_prof_id"] != "") {
                
                $profId = $sub->metadata["stripe_prof_id"];
                
                $profMg = new \spamtonprof\stp_api\StpProfManager();
                $prof = $profMg->get(array(
                    "stripe_id" => $profId
                ));
                
                if ($transfert_group) {
                    $messages[] = "Transfert déjà fait pour ce paiement";
                    return;
                }
                
                $messages[] = "Montant : " . $charge_amt / 100 . "€";
                
                $commission = 25;
                if ($profId == "acct_1D0z7ZI85S4kxqgW") {
                    $commission = 20;
                }
                
                if ($discount) { // on change la commission
                    
                    $percent_off = $discount->coupon->percent_off;
                    
                    if ($percent_off >= 100) {
                        $slack->sendMessages('log', array(
                            "Event id : " . $event_json->id,
                            "Nouveau paiement rÃ©ussi",
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
                
                // changement de la commission en fonction du solde
                $act = $this->retrieve_act($profId);
                $solde = doubleval($act->metadata["solde"]);
                
                $info = array(
                    "solde" => array(
                        "solde" => $solde,
                        "com" => $commission,
                        'charge_amt' => $charge_amt
                    )
                );
                
                $com_solde = $this->determine_com($info) / 100;
                $com = $commission / 100;
                
                $part_com = round($charge_amt * $com);
                $part_prof = round($charge_amt * (1 - ($com + $com_solde)));
                $part_solde = round($charge_amt * $com_solde);
                
                $new_solde = $solde - $part_solde / 100;
                
                $act->metadata["solde"] = $new_solde;
                $act->save();
                
                if ($part_prof > 0) {
                    
                    // on rÃ©cupÃ¨re le payement intent pour mettre Ã  jour son transfer group
                    // $PaymentIntentId = $charge->payment_intent;
                    // $PaymentIntent = \Stripe\PaymentIntent::retrieve($PaymentIntentId);
                    
                    // $PaymentIntent->transfer_group = $chargeId;
                    // $PaymentIntent->save();
                    
                    $charge->transfer_group = $chargeId;
                    $charge->save();
                    
                    // on transfÃ¨re 75 % au prof
                    \Stripe\Transfer::create(array(
                        "amount" => $part_prof,
                        "currency" => "eur",
                        "destination" => $profId,
                        "transfer_group" => $chargeId,
                        "source_transaction" => $chargeId
                    ));
                }
                
                $messages[] = "Transfert vers : " . $profId . " (" . $prof->getEmail_stp() . ")  de " . $part_prof / 100 . " € à " . round((1 - $com - $com_solde) * 100, 2) . "% réussi";
                
                if ($part_solde > 0) {
                    $messages[] = "Passage de la com de " . round($com * 100, 2) . "% à " . round(($com + $com_solde) * 100, 2) . "% pour régularisation du solde ";
                    $messages[] = $part_solde / 100 . " € utilisé pour régulariser le solde";
                    $messages[] = "Le nouveau solde est de : " . $new_solde . " € ( il était de " . $solde . " € )";
                }
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

    public function retrieve_act($stripe_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $act = \Stripe\Account::retrieve($stripe_id);
        return ($act);
    }

    public function retrieve_charge($charge_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $charge = \Stripe\Charge::retrieve($charge_id);
        return ($charge);
    }

    public function retrieve_invoice($invoice_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $invoice = \Stripe\Invoice::retrieve($invoice_id);
        return ($invoice);
    }

    public function __construct($testMode = true)
    
    {
        if (gettype($testMode) == "string") {
            
            $testMode = ($testMode === 'true');
        }
        
        $this->testMode = $testMode;
    }

    public function retrieve_event($id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $ret = \Stripe\Event::retrieve($id);
        
        return ($ret);
    }

    public function retrieveInvoice()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $invoice = \Stripe\Invoice::retrieve("in_1DqhYrIcMMHYXO98PryWln1j");
        prettyPrint($invoice);
    }

    public function getUnpaidInvoicesOfCancelSub()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $invoices = \Stripe\Invoice::all([
            "limit" => 3
        ]);
        ;
        
        // rÃ©cupÃ©ration de tous les abos stripes annulÃ©es
        $ref_abos = [];
        $params = [
            "limit" => 20,
            'status' => 'canceled'
        ];
        
        do {
            
            $subs = \Stripe\Subscription::all($params);
            $subs = $subs->data;
            
            foreach ($subs as $sub) {
                $id = $sub->id;
                $ref_abonnement = $sub->metadata['ref_abonnement'];
                if ($ref_abonnement) {
                    $ref_abos[] = [
                        'ref_abo' => $ref_abonnement,
                        'sub_id' => $id
                    ];
                }
                $params['starting_after'] = $id;
            }
        } while ($subs);
        
        $i = 0;
        
        $invoices_abo = [];
        
        // rÃ©cupÃ©ration de toutes les factures impayÃ©s failed des abos annulÃ©s
        foreach ($ref_abos as $ref_abo) {
            
            $sub_id = $ref_abo['sub_id'];
            $ref_abo = $ref_abo['ref_abo'];
            
            $invoices = \Stripe\Invoice::all([
                "limit" => 100,
                "subscription" => $sub_id
            ]);
            
            $invoices = $invoices->data;
            if ($invoices) {
                $links = [];
                $due = false;
                foreach ($invoices as $invoice) {
                    if (! $invoice->paid && $invoice->status == 'open') {
                        $links[] = $invoice->hosted_invoice_url;
                        $due = true;
                    }
                }
                if ($due) {
                    $invoices_abo[$ref_abo] = $links;
                }
            }
            
            $i = $i + 1;
        }
        
        // affichage
        $ref_abos = array_keys($invoices_abo);
        $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
        
        $constructor = array(
            "construct" => array(
                'ref_parent',
                'ref_eleve'
            )
        );
        
        $abos = $aboMg->getAll(array(
            'actif_account',
            'ref_abos' => $ref_abos
        ), $constructor);
        
        foreach ($abos as $abo) {
            if ($abo->getProche()) {
                echo ($abo->getProche()->getEmail() . ' : ' . $abo->getEleve()->getEmail() . '<br>');
            } else {
                echo ($abo->getEleve()->getEmail() . '<br>');
            }
            $links = $invoices_abo[$abo->getRef_abonnement()];
            foreach ($links as $link) {
                echo ($link . '<br>');
            }
            echo ('<br><br>');
        }
        
        // prettyPrint($abos);
    }

    public function getObjectId($id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $ret = \Stripe\Event::retrieve($id);
        
        return ($ret->data->object->id);
    }

    public function is_in_trial($sub_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $sub = \Stripe\Subscription::retrieve($sub_id);
        
        return ($sub->status == 'trialing');
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
                
                "Nouvel abonnement, bien jouÃ© la team !!",
                
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
                
                "Oops un paiement pour abonnement vient d'Ã©chouer",
                
                "ref compte : " . $refCompte,
                
                "email client : " . $emailClient,
                
                "Faut voir Ã§a avec le client",
                
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
        
        // mise Ã  jour de l'abonnement stripe
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
         * *********** Ã  Ã©xecuter pour mise en prod stripe - sert Ã  ajouter les produits Ã  stripe et enregistrer les reds dans la bdd ***************************
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
         * *********** fin Ã  Ã©xecuter pour mise en prod stripe - sert Ã  ajouter les produits Ã  stripe et enregistrer les reds dans la bdd ***************************
         */
        
        // /************* deb : Ã  Ã©xecuter pour mise en prod stripe - sert Ã  ajouter les plan de paiements Ã  stripe et enregistrer les refs dans la bdd ****************************/
        
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
        
        // faire la crÃ©ation du compte stripe
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
        
        // faire la crÃ©ation du compte stripe
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
        
        // faire la crÃ©ation du compte stripe
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

    public function getChargeWithoutTransfer()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $ref_abos = [];
        $params = [
            "limit" => 20
        ];
        
        do {
            
            $charges = \Stripe\Charge::all($params);
            prettyPrint($charges);
            $subs = $charges->data;
            
            foreach ($subs as $sub) {
                $id = $sub->id;
                $ref_abonnement = $sub->metadata['ref_abonnement'];
                if ($ref_abonnement) {
                    $ref_abos[] = [
                        'ref_abo' => $ref_abonnement,
                        'sub_id' => $id
                    ];
                }
                $params['starting_after'] = $id;
            }
        } while ($subs);
    }

    /* pour mettre Ã  jour la cb d'un compte stripe */
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

    // pour crÃ©er tous les produits et les plans dÃ©finis dans la base stp qui ne sont pas dans stripe
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
            
            // crÃ©er la formule dans stripe
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
        
        \Stripe\Subscription::update($subId, [
            'trial_end' => $endDay->getTimestamp(),
            'prorate' => $prorate
        ]);
    }

    public function sendInvoice($customer_id, $after)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        
        $after = \DateTime::createFromFormat('j/m/Y', $after);
        $after = $after->getTimestamp();
        
        $invoices = \Stripe\Invoice::all([
            "customer" => $customer_id,
            "date" => array(
                "gt" => $after
            ),
            "limit" => 50
        ]);
        
        $invoices = $invoices->data;
        
        foreach ($invoices as $invoice) {
            
            echo ($invoice->invoice_pdf . "<br>");
        }
        
        echo ('<br><br><br><br> Link to pay <br><br>');
        
        foreach ($invoices as $invoice) {
            
            echo ($invoice->hosted_invoice_url . "<br>");
        }
        
        // $invoice = \Stripe\Invoice::retrieve("in_1CFfetIcMMHYXO986qA1Rhuu");
        //
    }
}
