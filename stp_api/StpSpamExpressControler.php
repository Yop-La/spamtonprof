<?php
namespace spamtonprof\stp_api;

class StpSpamExpressControler

{

    private $test_mode = true;

    // private $_db;
    public function __construct($test_mode)
    {
        $this->test_mode = $test_mode;
    }

    public function process_paid_order($session_id)
    {
        $slack = new \spamtonprof\slack\Slack();

        $slack->sendMessages('spam-express', array(
            '-------',
            'Processing a spam express order ...'
        ));

        $stripeMg = new \spamtonprof\stp_api\StripeManager($this->test_mode);

        $session = $stripeMg->retrieve_session($session_id);

        $payment_intent_id = $session->payment_intent;

        $metadata = $session->metadata;

        $cmd_id = $metadata["cmd_id"];
        $ref_offre = $metadata['ref_offre'];

        $constructor = array(
            "construct" => array(
                'ref_lead',
                'ref_pole',
                'ref_prof',
                'ref_offre'
            )
        );

        $cmdMg = new \spamtonprof\stp_api\StpCmdSpamExpressManager();
        $cmd = $cmdMg->get($cmd_id);

        $cmd->setRef_offre($ref_offre);
        $cmdMg->update_ref_offre($cmd);

        $cmd->setPayment_intent_id($payment_intent_id);
        $cmdMg->update_payment_intent_id($cmd);

        $cmdMg = new \spamtonprof\stp_api\StpCmdSpamExpressManager();
        $cmd = $cmdMg->get($cmd_id, $constructor);

        $offre = $cmd->getOffre();
        $pole = $cmd->getPole();

        $slack->sendMessages('spam-express', array(
            'Order id: ' . $cmd->getRef_cmd(),
            'Ref offre:' . $ref_offre
        ));

        $lead = $cmd->getLead();

        // update order status and attribute teacher

        $cmd->setStatus('paid');
        $cmdMg->update_status($cmd);

        $prof = $cmd->getProf();

        $email_prof = $prof->getEmail_stp();

        // transfert amount to connected account
        try {

            $slack->sendMessages('spam-express', array(
                'Transfert vers: ' . $email_prof,
                'du paiement ' . $payment_intent_id
            ));

            $transfert_id = false;
            $transfert_id = $stripeMg->transfert_prof($payment_intent_id, $prof->get_stripe_id($this->test_mode), $email_prof);
        } catch (\Exception $e) {

            $slack->sendMessages('spam-express', array(
                'Fail: Connect Transfert vers: ' . $email_prof,
                $e->getMessage(),
                $e->getCode()
            ));

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

            $params = [];
            $params['payment_intent_id'] = $payment_intent_id;
            $params['name'] = $prof->getPrenom();

            try {

                $cmd->setStatus('transfert_fail');
                $cmdMg->update_status($cmd);

                $email->addTo($email_prof, $prof->getPrenom(), $params, 0);
                $email->addCc("alexandre@spamtonprof.com");

                $email->setTemplateId("d-c273e358663b41cc96e7443a98d424ae");
                $sendgrid = new \SendGrid(SEND_GRID_API_KEY);

                $response = $sendgrid->send($email);

                echo ($response->statusCode());
            } catch (\Exception $e) {
                $slack->sendMessages('spam-express', array(
                    "Fail: Envoi de l'email d'échec de transfert:",
                    $e->getMessage()
                ));
            }
        }

        if ($transfert_id) {

            $cmd->setTransfert_id($transfert_id);
            $cmdMg->update_transfert_id($cmd);

            $slack->sendMessages('spam-express', array(
                "Transfert " . $transfert_id . "réussi"
            ));

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

            $params = [];
            $params['name'] = $prof->getPrenom();
            $params['email_eleve'] = $lead->getEmail();
            $params["payment_intent_id"] = $payment_intent_id;
            $params["transfert_id"] = $transfert_id;
            $params["service"] = "SpamExpress: " . $offre->getName() . " - " . $offre->getTitle() . " - " . $pole->getName();

            $cmd->setStatus('transfert_done');
            $cmdMg->update_status($cmd);

            try {

                $email->addTo($email_prof, $prof->getPrenom(), $params, 0);
                $email->addCc("alexandre@spamtonprof.com");

                $email->setTemplateId("d-a828ef8131724bc9b080a93678a9014d");
                $sendgrid = new \SendGrid(SEND_GRID_API_KEY);

                $response = $sendgrid->send($email);

                echo ($response->statusCode());
            } catch (\Exception $e) {

                $slack->sendMessages('spam-express', array(
                    "Fail: Envoi de l'email de transfert réussi",
                    $e->getMessage()
                ));
            }
        } else {
            $slack->sendMessages('spam-express', array(
                "Echec du transfert ..."
            ));
        }

        $slack->sendMessages('spam-express', array(
            "Envoi de l'email de mise en relation"
        ));

        // send email to teacher and student
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("alexandre@spamtonprof.com", "Alexandre de SpamTonProf");

        $params = [];
        $params['email_prof'] = $email_prof;
        $params["prof_name"] = $prof->getPrenom();

        $params["title_offre"] = $offre->getTitle();
        $params["name_offre"] = $offre->getName();
        $params["matieres"] = $pole->getName();
        $params['remarque'] = $cmd->getRemarque();
        $params['name'] = $lead->getName();

        try {

            $email->addTo($lead->getEmail(), $lead->getName(), $params, 0);
            $email->addCc($email_prof);
            $email->addCc('alexandre@spamtonprof.com');

            $email->setTemplateId("d-9eb5d54a97894ba69760be86104da476");
            $sendgrid = new \SendGrid(SEND_GRID_API_KEY);

            $response = $sendgrid->send($email);

            echo ($response->statusCode());
        } catch (\Exception $e) {

            $slack->sendMessages('spam-express', array(
                "Fail: Envoi de l'email de mise en relation:",
                $e->getMessage()
            ));
        }

        $slack->sendMessages('spam-express', array(
            "Fini"
        ));
    }
}