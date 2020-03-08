<?php
namespace spamtonprof\stp_api;

use PDO;
use DateTime;
use Exception;

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
 */
class StripeManager

{

    private $testMode = true, $slack, $stripe_account = false;

    public function stopSubscription($subscriptionId)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $subscription = \Stripe\Subscription::retrieve($subscriptionId);

        if ($subscription->status == "canceled") {
            return;
        }

        $subscription->cancel();
    }

    public function updateStripeProfId(string $subId, string $stripeProdId)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $sub = \Stripe\Subscription::retrieve($subId);

        $sub->metadata["stripe_prof_id"] = $stripeProdId;

        $sub->save();
    }

    public function new_prof_invoice($email_client, $email_prof = 'sebastien@spamtonprof.com', $amount = '2000', $description = 'test')
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $customers = \Stripe\Customer::all([
            "limit" => 3,
            "email" => $email_client
        ]);

        $customers = $customers->data;
        $customer_id = false;

        // récupération - création du customer
        if ($customers) {
            $customer_id = $customers[0]->id;
        } else {
            $customer = \Stripe\Customer::create([
                "email" => $email_client
            ]);
            $customer_id = $customer->id;
        }

        \Stripe\InvoiceItem::create([
            "customer" => $customer_id,
            "amount" => $amount,
            "currency" => "eur",
            "description" => $description
        ]);

        $invoice = \Stripe\Invoice::create([
            "customer" => $customer_id,
            "billing" => "send_invoice",
            "custom_fields" => array(
                array(
                    'name' => 'email_prof',
                    'value' => $email_prof
                )
            ),
            "days_until_due" => 2
        ]);

        $invoice->sendInvoice();
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

            // si le prof doit de l'argent à spamtonprof
            if ($solde > 0) {

                $part_prof_before = (1 - $com) * $charge_amt;

                if ($solde >= $part_prof_before) {

                    $com_solde = 1 - $com;
                } else {

                    $com_solde = $solde / $charge_amt;
                }
            }

            return (100 * $com_solde);
        }
    }

    public function transfert_custom_facture($event_json, $email_prof)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $slack = new \spamtonprof\slack\Slack();

        $messages = [];

        $chargeId = $event_json->data->object->charge;
        $lines = $event_json->data->object->lines->data;
        $customer_email = $event_json->data->object->customer_email;

        try {

            $messages[] = "Nouveau paiement réussi";
            foreach ($lines as $line) {
                $description = $line->description;
                $messages[] = "Description facture : " . $description;
            }

            $messages[] = "chargeId : " . $chargeId;
            $messages[] = "client : " . $customer_email;

            $charge = \Stripe\Charge::retrieve($chargeId);
            $charge_amt = $charge->amount;

            $profMg = new \spamtonprof\stp_api\StpProfManager();
            $prof = $profMg->get(array(
                "email_stp" => $email_prof
            ));

            $profId = $prof->getStripe_id();
            if ($this->testMode) {
                $profId = $prof->getStripe_id_test();
            }

            $commission = 25;
            if ($profId == "acct_1D0z7ZI85S4kxqgW") {
                $commission = 20;
            }

            $part_prof = round($charge_amt * (1 - ($commission / 100)));

            if ($part_prof > 0) {

                $charge->transfer_group = $chargeId;
                $charge->save();

                // on transfère au prof
                \Stripe\Transfer::create(array(
                    "amount" => $part_prof,
                    "currency" => "eur",
                    "destination" => $profId,
                    "transfer_group" => $chargeId,
                    "source_transaction" => $chargeId
                ));
            }

            $messages[] = "Montant : " . $charge_amt / 100 . "€";

            $charge->metadata['part_prof'] = $part_prof;
            $charge->save();

            $messages[] = "Transfert vers : " . $profId . " (" . $prof->getEmail_stp() . ")  de " . $part_prof / 100 . " € à " . (100 - $commission) . "% réussi";

            if ($this->testMode) {
                $messages[] = 'PS : ceci est un test. Désolé :p :p ';
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        } finally {

            $slack->sendMessages("stripe", $messages);

            if ($prof) {

                $body = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/info-paiement-prof.html");
                $body = str_replace("[[prof-name]]", $prof->getPrenom(), $body);
                $body = str_replace("[[details-paiement]]", nl2br(implode("\n", $messages)), $body);

                $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
                $smtp = $smtpMg->get(array(
                    "ref_smtp_server" => $smtpMg::smtp2Go
                ));
                $smtp->sendEmail("Un paiement vient d'être réalisé", $prof->getEmail_stp(), $body, "alexandre@spamtonprof.com", "Alex de SpamTonProf", true, array(
                    "alexandre@spamtonprof.com"
                ));
            }
        }
    }

    /*
     *
     * 2 cas d'utilisation :
     * - transfert suite à facturation d'abonnement auto
     * - transfert manuel avec abonnement associé à la charge : $event_json = false, $chargeIdMan
     *
     *
     *
     */
    public function transfertSubscriptionCharge($event_json, $chargeIdMan = false)
    {
        serializeTemp($event_json);
        $slack = new \spamtonprof\slack\Slack();

        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $discount = false;
        $str_abo = false;
        $prof = false;

        $messages = [];
        $messages[] = "---------";

        $chargeId = $chargeIdMan;
        $subId = false;

        try {

            if ($event_json && ! $chargeIdMan) {
                $messages[] = "Event id : " . $event_json->id;
                $chargeId = $event_json->data->object->charge;
                $subId = $event_json->data->object->subscription;
                $discount = $event_json->data->object->discount;
                $amount_paid = $event_json->data->object->amount_paid;

                if ($amount_paid == 0) {
                    $messages[] = "Facture d'un montant nul";
                    return;
                }

                if (! $subId) {

                    $messages[] = "Facture sans abonnement. Faire transfert manuellement si nécessaire";
                    return;
                }
            } else {
                $messages[] = "Transfert manuel";
            }

            $charge = \Stripe\Charge::retrieve($chargeId);
            $charge_amt = $charge->amount;

            $messages[] = "Nouveau paiement réussi";
            $messages[] = "chargeId : " . $chargeId;

            if (! $subId) {
                $invoice = $this->retrieve_invoice($charge->invoice);
                $subId = $invoice->subscription;
            }

            $sub = \Stripe\Subscription::retrieve($subId);

            $ref_abonnement = $sub->metadata['ref_abonnement'];

            if ($ref_abonnement) {

                $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();

                $constructor = array(
                    "construct" => array(
                        'ref_eleve',
                        'ref_parent',
                        'ref_formule',
                        'ref_plan'
                    ),
                    "ref_eleve" => array(
                        "construct" => array(
                            'ref_niveau'
                        )
                    )
                );

                $abo = $aboMg->get(array(
                    'ref_abonnement' => $ref_abonnement
                ), $constructor);

                $str_abo = strip_tags($abo->__toString());
            } else {
                $messages[] = "Oups, pas d'abonnement associé à ce paiement ...";
            }

            if ($sub->metadata["stripe_prof_id"] != "") {

                $profId = $sub->metadata["stripe_prof_id"];

                $profMg = new \spamtonprof\stp_api\StpProfManager();
                $prof = $profMg->get(array(
                    "stripe_id" => $profId
                ));

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

                if ($part_prof > 0) {

                    // on rÃ©cupÃ¨re le payement intent pour mettre Ã  jour son transfer group
                    // $PaymentIntentId = $charge->payment_intent;
                    // $PaymentIntent = \Stripe\PaymentIntent::retrieve($PaymentIntentId);

                    // $PaymentIntent->transfer_group = $chargeId;
                    // $PaymentIntent->save();

                    $charge->transfer_group = $chargeId;
                    $charge->save();

                    // on transfère au prof
                    \Stripe\Transfer::create(array(
                        "amount" => $part_prof,
                        "currency" => "eur",
                        "destination" => $profId,
                        "transfer_group" => $chargeId,
                        "source_transaction" => $chargeId
                    ));
                }
                $act->metadata["solde"] = $new_solde;
                $act->save();
                $charge->metadata['part_prof'] = $part_prof;
                $charge->save();

                $messages[] = "Transfert vers : " . $profId . " (" . $prof->getEmail_stp() . ")  de " . $part_prof / 100 . " € à " . round((1 - $com - $com_solde) * 100, 2) . "% réussi";

                if ($part_solde > 0) {
                    $messages[] = "Passage de la com de " . round($com * 100, 2) . "% à " . round(($com + $com_solde) * 100, 2) . "% pour régularisation du solde ";
                    $messages[] = $part_solde / 100 . " € utilisé pour régulariser le solde";
                    $messages[] = "Le nouveau solde est de : " . $new_solde . " € ( il était de " . $solde . " € )";
                }
            } else {
                $messages[] = "Cet abonnement vient d'être facturé sans compte prof associé";
            }

            if ($str_abo) {
                $messages[] = 'Pour ' . $str_abo;
            }
        } catch (\Exception $e) {

            $messages[] = $e->getMessage();
        } finally {
            $slack->sendMessages("stripe", $messages);

            if ($prof) {

                $body = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/info-paiement-prof.html");
                $body = str_replace("[[prof-name]]", $prof->getPrenom(), $body);
                $body = str_replace("[[details-paiement]]", nl2br(implode("\n", $messages)), $body);

                $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
                $smtp = $smtpMg->get(array(
                    "ref_smtp_server" => $smtpMg::smtp2Go
                ));
                $smtp->sendEmail("Un paiement vient d'être réalisé", $prof->getEmail_stp(), $body, "alexandre@spamtonprof.com", "Alex de SpamTonProf", true, array(
                    "alexandre@spamtonprof.com"
                ));
            }
        }
    }

    public function create_subscription_checkout_session($plan_strp_id, $customer_id, $meta_sub = false, $trial_end = 'now')
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $params_session = [
            'customer' => $customer_id,
            'payment_method_types' => [
                'card'
            ],
            'subscription_data' => [
                'items' => [
                    [
                        'plan' => $plan_strp_id
                    ]
                ]
            ],
            'success_url' => domain_to_url() . '/remerciement-abonnement/?ref_abo=' . $meta_sub["ref_abonnement"],
            'cancel_url' => domain_to_url() . '/dashboard-eleve/?info=' . urlencode("Oups, le paiement a échoué,  veuillez réssayer ou contactez nous ! ")
        ];

        if ($trial_end != 'now') {
            $params_session['trial_end'] = $trial_end;
        }

        if ($meta_sub) {
            $params_session['subscription_data']['metadata'] = $meta_sub;
        }

        $session = \Stripe\Checkout\Session::create($params_session);

        return ($session->id);
    }

    public function create_checkout_session_to_update_payment_method($cus_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        if (! $cus_id) {
            return (false);
        }

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => [
                'card'
            ],
            'mode' => 'setup',
            'setup_intent_data' => [
                'metadata' => [
                    'customer_id' => $cus_id
                ]
            ],
            'success_url' => domain_to_url() . '/dashboard-eleve/?info=' . urlencode("Carte mise à jour"),
            'cancel_url' => domain_to_url() . '/dashboard-eleve/?info=' . urlencode("Oups, la mise à jour de la carte a échoué,  veuillez réssayer ou contactez nous ! ")
        ]);

        return ($session->id);
    }

    public function create_customer($email, $metadata)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $customer = \Stripe\Customer::create(array(

            'email' => $email,

            "metadata" => $metadata
        ));

        return ($customer);
    }

    public function add_customer($email)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $customers = \Stripe\Customer::all([
            'limit' => 3,
            'email' => $email
        ]);

        $customers = $customers->data;

        if (count($customers) == 1) {
            return ($customers[0]);
        }

        if (count($customers) > 1) {
            return (false);
        }

        $cus = \Stripe\Customer::create([
            'email' => $email
        ]);

        return ($cus);
    }

    public function retrieve_customer($stripe_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $cus = \Stripe\Customer::retrieve($stripe_id);
        return ($cus);
    }

    public function retrieve_setup_intent($id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $setupIntent = \Stripe\SetupIntent::retrieve($id);
        return ($setupIntent);
    }

    public function attach_payment_method($payment_method_id, $cus_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $payment_method = \Stripe\PaymentMethod::retrieve($payment_method_id);
        $payment_method->attach([
            'customer' => $cus_id
        ]);
    }

    public function set_default_payment_method($payment_method_id, $cus_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        \Stripe\Customer::update($cus_id, [
            'invoice_settings' => [
                'default_payment_method' => $payment_method_id
            ]
        ]);
    }

    public function retrieve_session($session_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        return ($session);
    }

    public function retrieve_act($stripe_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $act = \Stripe\Account::retrieve($stripe_id);
        return ($act);
    }

    public function retrieve_sub($sub_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $sub = \Stripe\Subscription::retrieve($sub_id);
        return ($sub);
    }

    public function retrieve_payout($id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $payout = false;
        if ($this->stripe_account) {

            $payout = \Stripe\Payout::retrieve($id, [
                'stripe_account' => $this->stripe_account
            ]);
        } else {
            $payout = \Stripe\Payout::retrieve($id);
        }

        return ($payout);
    }

    public function retrieve_balance_transaction($id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $charge = false;
        if ($this->stripe_account) {

            $charge = \Stripe\BalanceTransaction::retrieve($id, [
                'stripe_account' => $this->stripe_account
            ]);
        } else {
            $charge = \Stripe\BalanceTransaction::retrieve($id);
        }

        return ($charge);
    }

    public function retrieve_transfer($transfer_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $transfer = false;
        if ($this->stripe_account) {

            $transfer = \Stripe\Transfer::retrieve($transfer_id, [
                'stripe_account' => $this->stripe_account
            ]);
        } else {
            $transfer = \Stripe\Transfer::retrieve($transfer_id);
        }

        return ($transfer);
    }

    // à utiliser sur un compte connecté
    public function retrieve_source_charge_of_transaction($transaction_id)
    {
        $transaction = $this->retrieve_balance_transaction($transaction_id);

        $charge = false;

        switch ($transaction->type) {
            case 'payment':
                $charge = $this->retrieve_charge($transaction->source);
                break;
            case 'payment_refund':
                $refund = $this->retrieve_refund($transaction->source);
                $charge = $this->retrieve_charge($refund->charge);
                break;
            case 'payout':
                return (false);
            case 'payout_failure':
                return (false);
            default:
                prettyPrint(array(
                    "die bad transaction type",
                    $transaction->type
                ));
                break;
        }

        $stripe_plateforme = new \spamtonprof\stp_api\StripeManager(false);

        $transfer = $stripe_plateforme->retrieve_transfer($charge->source_transfer);

        $charge = $stripe_plateforme->retrieve_charge($transfer->source_transaction);

        return ($charge);
    }

    public function retrieve_refund($refund_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $refund = false;
        if ($this->stripe_account) {

            $refund = \Stripe\Refund::retrieve($refund_id, [
                'stripe_account' => $this->stripe_account
            ]);
        } else {
            $refund = \Stripe\Refund::retrieve($refund_id);
        }

        return ($refund);
    }

    public function retrieve_charge($charge_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $charge = false;
        if ($this->stripe_account) {

            $charge = \Stripe\Charge::retrieve($charge_id, [
                'stripe_account' => $this->stripe_account
            ]);
        } else {
            $charge = \Stripe\Charge::retrieve($charge_id);
        }

        return ($charge);
    }

    public function retrieve_invoice($invoice_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        $invoice = \Stripe\Invoice::retrieve($invoice_id);
        return ($invoice);
    }

    public function list_balance_transaction($payout, int $limit = 100, $starting_after = false)
    {
        $all_transactions = [];
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $params = [
            'limit' => $limit,
            'payout' => $payout
            // 'status' => 'paid'
        ];

        if ($starting_after) {
            $params['starting_after'] = $starting_after;
        }

        if ($this->stripe_account) {

            $all_transactions = \Stripe\BalanceTransaction::all($params, [
                'stripe_account' => $this->stripe_account
            ]);
        } else {
            $all_transactions = \Stripe\BalanceTransaction::all();
        }

        try {
            $all_transactions = $all_transactions->data;
        } catch (\Exception $e) {
            return (false);
        }

        return ($all_transactions);
    }

    public function list_payouts($starting_after = false, $ending_before = false, $arrival_gte = false, $arrival_lte = false, int $limit = 100)
    {
        $all_payouts = [];
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $params = [
            'limit' => $limit,
            'status' => 'paid'
        ];

        if ($starting_after) {
            $params['starting_after'] = $starting_after;
        }

        if ($ending_before) {
            $params['ending_before'] = $ending_before;
        }

        $arrival = array();

        if ($arrival_gte) {

            $arrival_gte = \DateTime::createFromFormat(FR_DATE_FORMAT, $arrival_gte);
            $arrival['gte'] = $arrival_gte->getTimestamp();
            $arrival_gte = $arrival_gte->format(FR_DATE_FORMAT);
        }

        if ($arrival_lte) {
            $arrival_lte = \DateTime::createFromFormat(FR_DATE_FORMAT, $arrival_lte);
            $arrival['lte'] = $arrival_lte->getTimestamp();
            $arrival_lte = $arrival_lte->format(FR_DATE_FORMAT);
        }

        if (count($arrival) != 0) {
            $params['arrival_date'] = $arrival;
        }

        if ($this->stripe_account) {

            $payouts = \Stripe\Payout::all($params, [
                'stripe_account' => $this->stripe_account
            ]);
        } else {
            $payouts = \Stripe\Payout::all($params);
        }

        // $invoices = \Stripe\Invoice::all($params);

        $slack = new \spamtonprof\slack\Slack();

        try {
            $all_payouts = $payouts->data;

            // if (count($all_payouts) == 100) {
            // $last_payout = $all_payouts[99];
            // $remaining_payouts = $this->list_payouts($arrival_gte, $arrival_lte, $last_payout->id, 100);
            // if ($remaining_payouts) {
            // return (array_merge($all_payouts, $remaining_payouts));
            // }
            // } else {
            // return ($all_payouts);
            // }
        } catch (\Exception $e) {
            return (false);
        }

        return ($all_payouts);
    }

    public function list_invoices(int $limit = 100, $starting_after = false)
    {
        $all_invoices = [];
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $params = [
            'limit' => $limit,
            'status' => 'paid'
        ];

        if ($starting_after) {
            $params['starting_after'] = $starting_after;
        }

        $invoices = \Stripe\Invoice::all($params);

        try {
            $all_invoices = $invoices->data;
        } catch (\Exception $e) {
            return ($all_invoices);
        }

        return ($all_invoices);
    }

    public function get_best_customers()
    {
        $slack = new \spamtonprof\slack\Slack();
        $last_id = false;
        $res = [];
        $nb_tour = 1;
        do {

            $invoices = $this->list_invoices(100, $last_id);

            foreach ($invoices as $invoice) {

                $cus_id = $invoice->customer;
                $amount_paid = $invoice->amount_paid;

                $status = $invoice->status;

                if ($status == 'paid') {

                    if (array_key_exists($cus_id, $res)) {
                        $res[$cus_id] = $res[$cus_id] + $amount_paid;
                    } else {
                        $res[$cus_id] = $amount_paid;
                    }
                    $last_id = $invoice->id;
                }
            }

            $slack->sendMessages('log', array(
                "last id : " . $last_id,
                "nb de tour : " . $nb_tour,
                "nb invoice : " . count($invoices)
            ));

            $nb_tour = $nb_tour + 1;
        } while (count($invoices) != 0);

        serializeTemp($res, "/tempo/res");

        $customers = unserializeTemp("/tempo/res");

        arsort($customers);

        $best_cus = [];
        $limit = 30;
        $counter = 0;
        foreach ($customers as $cus_id => $amnt) {

            $cus = $this->retrieve_customer($cus_id);
            $email = $cus->email;

            $best_cus[$email] = $amnt;

            $counter = $counter + 1;
            if ($counter > $limit) {
                break;
            }
        }

        prettyPrint($best_cus);
    }

    public function __construct($testMode = true, $prof_email = false)

    {
        $this->slack = new \spamtonprof\slack\Slack();

        if (gettype($testMode) == "string") {

            $testMode = ($testMode === 'true');
        }

        $this->testMode = $testMode;

        if ($prof_email) {

            $profMg = new \spamtonprof\stp_api\StpProfManager();
            $prof = $profMg->get(array(
                'email_stp' => $prof_email
            ));

            $this->stripe_account = $prof->getStripe_id();
            if ($this->testMode) {
                $this->stripe_account = $prof->getStripe_id_test();
            }
        }
    }

    public function retrieve_event($id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $ret = \Stripe\Event::retrieve($id);

        return ($ret);
    }

    public function delete_all_pending_invoice_items()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $items = \Stripe\InvoiceItem::all([
            'limit' => 100,
            'pending' => true
        ]);

        $items = $items->data;

        foreach ($items as $item) {

            $invoice_item = \Stripe\InvoiceItem::retrieve($item->id);
            $invoice_item->delete();
        }
    }

    public function retrieveAllInvoice($email)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $customers = \Stripe\Customer::all([
            "email" => $email
        ]);

        $cus = $customers->data[0];

        $invoices = \Stripe\Invoice::all(array(
            "customer" => $cus->id
        ));

        $invoices = $invoices->data;

        foreach ($invoices as $invoice) {

            echo ($invoice->invoice_pdf . '<br>');
        }
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

    public function addInstallmentPlan($emailClient, $source, $planStripeId, $stripeProfId, \spamtonprof\stp_api\StpCompte $compte)
    {
        $slack = new \spamtonprof\slack\Slack();

        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $plan_stripe = \Stripe\Plan::retrieve($planStripeId);

        // création du customer si il n'existe pas déjà
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

                    'source' => $source
                ));
            }

            // création de l'abonnement
            $subParams = array(

                "customer" => $customer->id,

                "items" => array(

                    array(

                        "plan" => $planStripeId
                    )
                ),

                "metadata" => array(

                    "stripe_prof_id" => $stripeProfId,

                    "installments" => 1
                )
            );

            $subscription = \Stripe\Subscription::create($subParams);

            $slack->sendMessages("paiement", array(

                "Nouveau paiement",

                "ref compte : " . $compte->getRef_compte(),

                "email client : " . $emailClient,

                "Paiement en " . $plan_stripe->metadata['installments'] . " fois.",

                "Ref abonnement stripe : " . $subscription->id
            ));

            return (array(
                "subId" => $subscription->id,
                "cusId" => $customer->id
            ));
        } catch (Exception $e) {

            $slack->sendMessages("paiement", array(

                "Oops un paiement pour vient d'échouer",

                "ref compte : " . $compte->getRef_compte(),

                "email client : " . $emailClient,

                "Faut voir ça avec le client",

                "Erreur : " . $e->getMessage()
            ));
        }

        return (false);
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

    // pour avoir les charges pas traités ( qui n'ont pas fait l'objet d'un transfert et/ou d'une régularisation de solde
    public function getUnhandledCharge($nb_iter)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());
        // $slack = new \spamtonprof\slack\Slack();

        $charge_ids = [];
        $params = [
            "limit" => 20
        ];
        $iter = 0;
        do {

            $charges = \Stripe\Charge::all($params);

            $charges = $charges->data;

            foreach ($charges as $charge) {
                $id = $charge->id;
                $amount = $charge->amount;
                $status = $charge->status;
                $transfer_group = $charge->transfer_group;
                $part_prof = $charge->metadata['part_prof'];

                $params['starting_after'] = $id;

                if ($amount <= 0 || $transfer_group || $status != 'succeeded' || $part_prof != null) {
                    continue;
                }
                // $slack->sendMessages('stripe', array("-----",'charge sans transfert : ' . $id));
                $charge_ids[] = $id;
            }
            $iter ++;
        } while ($nb_iter != $iter);
        return ($charge_ids);
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

    public function update_installment_plan($ref_plan)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $planMg = new \spamtonprof\stp_api\StpPlanManager();
        $plan = $planMg->get(array(
            'ref_plan' => $ref_plan
        ));

        $plan_stripe_id = $plan->getRef_plan_stripe();
        if ($this->testMode) {
            $plan_stripe_id = $plan->getRef_plan_stripe_test();
        }

        $installments = $plan->getInstallments();

        if ($installments) {
            \Stripe\Plan::update($plan_stripe_id, [
                'metadata' => [
                    'installments' => $installments
                ]
            ]);
        }
    }

    // pour creer tous les produits et les plans definis dans la base stp
    // attention les formules et plans doivent déjà existés dans la base stp
    /* $query = array('custom' => ' where ref_formule >= 150' ) */
    public function createProductsAndPlans($query, $formules_exits = true)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
        $planMg = new \spamtonprof\stp_api\StpPlanManager();

        $constructor = array(
            "construct" => array(
                'plans'
            )
        );

        $formules = $formuleMg->getAll($query, $constructor);

        foreach ($formules as $formule) {

            $strProduct = false;
            $ref_formule_stripe = false;

            // si les formules n'existent pas . Sinon ça veut dire qu'on ajoute des plans à cette formule
            if (! $formules_exits) {
                // crér la formule dans stripe
                $strProduct = \Stripe\Product::create(array(
                    "name" => "Ref " . $formule->getRef_formule() . ": " . $formule->getFormule(),
                    "type" => "service"
                ));

                if ($this->testMode) {
                    $formule->setRef_product_stripe_test($strProduct->id);
                    $formuleMg->updateRefProductStripeTest($formule);
                } else {
                    $formule->setRef_product_stripe($strProduct->id);
                    $formuleMg->updateRefProductStripe($formule);
                }
            } else {

                $ref_formule_stripe = $formule->getRef_product_stripe();
                if ($this->testMode) {
                    $ref_formule_stripe = $formule->getRef_product_stripe_test();
                }
                $strProduct = \Stripe\Product::retrieve($ref_formule_stripe);
            }

            // crér les plans dans stripe
            $plans = $formule->getPlans();
            foreach ($plans as $plan) {

                $plan = \spamtonprof\stp_api\StpPlan::cast($plan);

                $ref_plan_stripe = $plan->getRef_plan_stripe();
                if ($this->testMode) {
                    $$ref_plan_stripe = $plan->getRef_plan_stripe_test();
                }

                if (! $ref_plan_stripe) {

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

    public function addTrialTest($subId, $endDay, $prorate = false)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $endDay = \DateTime::createFromFormat(PG_DATETIME_FORMAT, $endDay);

        \Stripe\Subscription::update($subId, [
            'trial_end' => $endDay->getTimestamp(),
            'prorate' => $prorate
        ]);
    }

    public function stopTrial($subId)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        \Stripe\Subscription::update($subId, [
            'trial_end' => 'now'
        ]);
    }

    public function addTrial($subId, $endDay, $prorate = true)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $endDay = \DateTime::createFromFormat(PG_DATE_FORMAT, $endDay);

        \Stripe\Subscription::update($subId, [
            'trial_end' => $endDay->getTimestamp(),
            'prorate' => $prorate
        ]);
    }

    public function createInvoice($cus, $des, $metadata = false)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $params = [
            'customer' => $cus,
            'collection_method' => 'send_invoice',
            'days_until_due' => 1,
            'description' => $des
        ];

        if ($metadata) {
            $params['metadata'] = $metadata;
        }

        $invoice = \Stripe\Invoice::create($params);

        return ($invoice);
    }

    public function createInvoiceItem($cus, $amnt, $description, $currency = 'eur')
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $invoiceItem = \Stripe\InvoiceItem::create([
            'customer' => $cus,
            'amount' => $amnt,
            'currency' => $currency,
            'description' => $description
        ]);

        return ($invoiceItem);
    }

    public function createProfInvoice()
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        \Stripe\Invoice::create();
    }

    public function sendInvoice($invoice_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $invoice = \Stripe\Invoice::retrieve($invoice_id);

        $invoice->sendInvoice();
    }

    public function markUncollectible($invoice_id)
    {
        \Stripe\Stripe::setApiKey($this->getSecretStripeKey());

        $invoice = \Stripe\Invoice::retrieve($invoice_id);

        $invoice->markUncollectible();
    }
}
