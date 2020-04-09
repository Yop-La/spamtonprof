<?php
namespace spamtonprof\stp_api;

use PDO;
use PHPMailer\PHPMailer\Exception;

class StpAbonnementManager
{

    private $_db, $eleveMg;

    const abos_en_cours_dun_prof = 'abos_en_cours_dun_prof', all_actif_abos_of_account = 'all_actif_abos_of_account', to_relaunch = 'to_relaunch';

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpAbonnement $StpAbonnement)
    {
        $q = $this->_db->prepare('insert into stp_abonnement(ref_eleve, ref_formule, ref_statut_abonnement, date_creation, remarque_inscription, ref_plan, test) values( :ref_eleve,:ref_formule,:ref_statut_abonnement,:date_creation,:remarque_inscription,:ref_plan, :test)');
        $q->bindValue(':ref_eleve', $StpAbonnement->getRef_eleve());
        $q->bindValue(':ref_formule', $StpAbonnement->getRef_formule());
        $q->bindValue(':ref_statut_abonnement', $StpAbonnement->getRef_statut_abonnement());
        $q->bindValue(':date_creation', $StpAbonnement->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':remarque_inscription', $StpAbonnement->getRemarque_inscription());
        $q->bindValue(':ref_plan', $StpAbonnement->getRef_plan());
        $q->bindValue(':test', $StpAbonnement->getTest(), PDO::PARAM_BOOL);
        $q->execute();

        $StpAbonnement->setRef_abonnement($this->_db->lastInsertId());

        return ($StpAbonnement);
    }

    public function stopSubscription($refAbonnement, $testMode)
    {

        // on récupère l'abonnement
        $constructor = array(
            "construct" => array(
                'ref_prof',
                'ref_eleve',
                'ref_parent',
                'ref_formule'
            )
        );

        $abonnement = $this->get(array(
            "ref_abonnement" => $refAbonnement
        ), $constructor);

        $eleve = $abonnement->getEleve();
        $proche = $abonnement->getProche();
        $prof = $abonnement->getProf();
        $formule = $abonnement->getFormule();

        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);
        $prof = \spamtonprof\stp_api\StpProf::cast($prof);

        if ($proche) {
            $proche = \spamtonprof\stp_api\StpProche::cast($proche);
        }
        $formule = \spamtonprof\stp_api\StpFormule::cast($formule);

        // résilier abonnement stripe
        $stripeMg = new \spamtonprof\stp_api\StripeManager($testMode);
        $stripeMg->stopSubscription($abonnement->getSubs_Id());

        // statut abonnement de actif à pas actif
        $abonnement->setRef_statut_abonnement($abonnement::TERMINE);
        $this->updateRefStatutAbonnement($abonnement);

        $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
        $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
            "ref_abonnement" => $abonnement->getRef_abonnement(),
            "ref_statut_abo" => $abonnement->getRef_statut_abonnement()
        )));

        // envoyer mails de résiliation à famille + prof (pour demander temoignage)

        $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
        $smtp = $smtpMg->get(array(
            "ref_smtp_server" => $smtpMg::smtp2Go
        ));
        $expeMg = new \spamtonprof\stp_api\StpExpeManager();
        $expe = $expeMg->get("info@spamtonprof.com");

        if ($eleve->hasToSendToParent()) {
            $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resiliation_abonnement_parent.html");
            $body_parent = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_parent);
            $body_parent = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()), $body_parent);
            $body_parent = str_replace("[[name]]", ucfirst($proche->getPrenom()), $body_parent);
            $body_parent = str_replace("[[formule]]", $formule->getFormule(), $body_parent);

            $smtp->sendEmail("C'est fait : l'abonnement de " . $eleve->getPrenom() . " est résilié.", $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }

        if ($eleve->hasToSendToEleve()) {
            $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resiliation_abonnement_eleve.html");
            $body_eleve = str_replace("[[name]]", ucfirst($eleve->getPrenom()), $body_eleve);
            $body_eleve = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_eleve);
            $body_eleve = str_replace("[[formule]]", $formule->getFormule(), $body_eleve);
            $smtp->sendEmail("C'est fait : ton abonnement est résilié.", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }

        // envoi prof
        $body_prof = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/resilier_prof.html");
        $body_prof = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()), $body_prof);
        $body_prof = str_replace("[[formule]]", $formule->getFormule(), $body_prof);
        $body_prof = str_replace("[[name]]", ucfirst($prof->getPrenom()), $body_prof);
        $body_prof = str_replace("[[adresse_eleve]]", $eleve->getEmail(), $body_prof);

        if ($proche) {
            $body_prof = str_replace("[[adresse_parent]]", $proche->getEmail(), $body_prof);
        }

        $smtp->sendEmail("Tu peux récupérer un témoignage ! ", $prof->getEmail_stp(), $body_prof, $expe->getEmail(), "Alexandre de SpamTonProf", true);

        // mise à jour de l'index
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

        $constructor = array(
            "construct" => array(
                'ref_statut_abonnement'
            )
        );

        $algoliaMg->updateAbonnement($abonnement->getRef_abonnement(), $constructor);
    }

    /*
     * pour remonter les abonnements sans prof dans le dashboard choisir prof
     */
    public function getAbonnementsToAssign()
    {
        $abonnements = [];

        $q = $this->_db->prepare("select * from stp_abonnement 
                where (first_prof_assigned = false or first_prof_assigned is null)
                    and ref_statut_abonnement = 2 
                    and (debut_essai is null or date(now())  + interval '1 days' >= debut_essai)
                    order by date_creation ");

        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);

            $this->construct(array(
                "objet" => $abonnement,
                "construct" => array(
                    'ref_eleve',
                    'ref_parent',
                    'ref_formule',
                    'ref_prof',
                    'ref_statut_abonnement'
                ),
                "ref_eleve" => array(
                    "construct" => array(
                        'ref_niveau'
                    )
                )
            ));
            $abonnements[] = $abonnement;
        }
        return ($abonnements);
    }

    /*
     * pour retourner les abonnements dont la periode d'essai est termine
     */
    public function getTrialCompleted()
    {
        $abonnements = [];

        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

        $q = $this->_db->prepare("select * from stp_abonnement where ref_statut_abonnement = 2 and fin_essai + integer '1' = :now");
        $q->bindValue(':now', $now->format(PG_DATE_FORMAT));
        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);

            $this->construct(array(
                "objet" => $abonnement,
                "construct" => array(
                    'ref_eleve',
                    'ref_formule',
                    'ref_parent',
                    'ref_prof'
                )
            ));
            $abonnements[] = $abonnement;
        }
        return ($abonnements);
    }

    /*
     * pour recuperer le nombre de message des abonnements durant les 7 derniers jours
     */
    public function getNbMessage()
    {
        $nbMessages = [];

        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        $oneWeekAgo = $now->sub(new \DateInterval("P7D"));

        $q = $this->_db->prepare("
            select ref_abonnement, count(ref_abonnement)  as nb_message from stp_message_eleve
                where date_message >= :one_week_ago
                group by ref_abonnement
                order by nb_message desc");
        $q->bindValue(':one_week_ago', $oneWeekAgo->format(PG_DATETIME_FORMAT));
        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {
            $nbMessages[] = array(
                "ref_abonnement" => $data["ref_abonnement"],
                "nb_message" => $data["nb_message"]
            );
        }
        return ($nbMessages);
    }

    /*
     * pour interrompre un abonnement
     *
     * date de fin : date de fin de l'essai ( = date de facturation)
     * la date de debut doit etre au minimum celle d'aujourdh'ui si avant 20h ( le cron tourne a 20h )
     */
    function interrupt($debut, $fin, $refAbo, $prorate = true, $prolongation = false)
    {
        $interruMg = new \spamtonprof\stp_api\StpInterruptionManager();

        $debut = \DateTime::createFromFormat('j/m/Y', $debut);
        $fin = \DateTime::createFromFormat('j/m/Y', $fin);

        if (! $prolongation) {

            $interru = new \spamtonprof\stp_api\StpInterruption(array(
                "debut" => $debut->format(PG_DATE_FORMAT),
                "fin" => $fin->format(PG_DATE_FORMAT),
                "prorate" => $prorate,
                "ref_abonnement" => $refAbo
            ));

            $interruMg->add($interru);
        } else {

            $interru = $interruMg->get(array(
                'currentOrNextInterruption' => $refAbo
            ));

            if ($interru) {

                $interru->setProlongation($debut->format(PG_DATE_FORMAT));
                $interru->setFin($fin->format(PG_DATE_FORMAT));

                $interruMg->updateFin($interru);
                $interruMg->updateProlongation($interru);
            } else {
                echo ("aucune interruption trouvé. L'interruption originale n'a pa dû être réalisé");
            }
        }
    }

    public function get_full_abo($refAbo)
    {
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

        $stpAbo = $this->get(array(
            "ref_abonnement" => $refAbo
        ), $constructor);

        return ($stpAbo);
    }

    public function toAlgoliaSupport($refAbo)
    {
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

        $stpAbo = $this->get(array(
            "ref_abonnement" => $refAbo
        ), $constructor);

        $eleve = $stpAbo->getEleve();
        $this->prenom = $eleve->getPrenom() . " " . $eleve->getNom();
        return ($stpAbo);
    }

    /*
     *
     * pour remettre a zéro messages tous les abonnements
     *
     */
    public function resetNbMessage()
    {
        $q = $this->_db->prepare("update stp_abonnement set nb_message = 0 where nb_message != 0 OR nb_message is null");
        $q->execute();
    }

    /*
     *
     * pour remettre l'abonnement dans l'état qu'il était avant de passer par le script finish_trial_inscription
     * c'est à dire dans l'état qu'il était après soumission du formulaire ( avant choix-prof )
     *
     */
    public function reverve_finish_trial_inscription($ref_abo, $debut_essai)
    {
        $constructor = array(
            "construct" => array(
                'ref_eleve'
            )
        );

        $abo = $this->get(array(
            'ref_abonnement' => $ref_abo
        ), $constructor);

        $ref_statut = $abo->getRef_statut_abonnement();

        if ($ref_statut != $abo::ESSAI && $ref_statut != $abo::TERMINE && $ref_statut != $abo::ATTENTE_DEMARRAGE) {
            throw new Exception("Pas possible d'appliquer cette fonction à des abos pas en essai ou pas terminé");
        }

        $abo->setRef_statut_abonnement($abo::ESSAI);
        $this->updateRefStatutAbonnement($abo);

        $begin = \DateTime::createFromFormat(FR_DATE_FORMAT, $debut_essai, new \DateTimeZone("Europe/Paris"));
        $abo->setDebut_essai($begin->format(PG_DATE_FORMAT));

        $end = clone $begin;

        $end = $end->add(new \DateInterval('P7D'));
        $abo->setFin_essai($end->format(PG_DATE_FORMAT));

        $this->updateDebutEssai($abo);
        $this->updateFinEssai($abo);

        $abo->setFirst_prof_assigned(false);
        $this->updateFirstProfAssigned($abo);

        $abo->setRef_prof(null);
        $this->updateRefProf($abo);

        $abo->setDate_attribution_prof(null);
        $this->updateDateAttributionProf($abo);

        $eleve = $abo->getEleve();
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

        $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
        $eleve->setSeq_email_parent_essai(0);
        $eleveMg->updateSeqEmailParentEssai($eleve);

        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

        $algoliaMg->deleteAbo($abo->getObjectID());
        $algoliaMg->addAbonnement($abo->getRef_abonnement());
    }

    // pour remonter les abonnements qui viennent de se voir attribuer un prof pour la premiere fois apres l'inscription
    public function getHasNotFirstProfAssignement()
    {
        $abonnements = [];

        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

        $q = $this->_db->prepare("select * from stp_abonnement 
                where first_prof_assigned = false and ( date_attribution_prof <= :now )");
        $q->bindValue(":now", $now->format(PG_DATETIME_FORMAT));

        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);

            $this->construct(array(
                "objet" => $abonnement,
                "construct" => array(
                    'ref_eleve',
                    'ref_formule',
                    'ref_prof',
                    'ref_parent',
                    'ref_plan'
                ),
                "ref_eleve" => array(
                    "construct" => array(
                        'ref_niveau'
                    )
                )
            ));
            $abonnements[] = $abonnement;
        }
        return ($abonnements);
    }

    public function updateRefProf(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_prof = :ref_prof where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":ref_prof", $abonnement->getRef_prof());
        $q->execute();
    }

    public function updateDernierContact(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set dernier_contact = :dernier_contact where ref_abonnement = :ref_abonnement");
        $q->bindValue(":dernier_contact", $abonnement->getDernier_contact());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateRefStatutAbonnement(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_statut_abonnement = :ref_statut_abonnement where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":ref_statut_abonnement", $abonnement->getRef_statut_abonnement());
        $q->execute();
    }

    public function updateSubsId(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set subs_id = :subs_id where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":subs_id", $abonnement->getSubs_Id());
        $q->execute();
    }

    public function updateTeleprospection(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set teleprospection = :teleprospection where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":teleprospection", $abonnement->getTeleprospection());
        $q->execute();
    }

    public function updateDateAttributionProf(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set date_attribution_prof = :date_attribution_prof where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        if ($abonnement->getDate_attribution_prof()) {
            $q->bindValue(":date_attribution_prof", $abonnement->getDate_attribution_prof()
                ->format(PG_DATETIME_FORMAT));
        } else {
            $q->bindValue(":date_attribution_prof", $abonnement->getDate_attribution_prof());
        }
        $q->execute();
    }

    public function updateRefProche(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_proche = :ref_proche where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":ref_proche", $abonnement->getRef_proche());
        $q->execute();
    }

    public function updateRelanceDate(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set relance_date = :relance_date where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":relance_date", $abonnement->getRelance_date());
        $q->execute();
    }

    public function updateToRelaunch(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set to_relaunch = :to_relaunch where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":to_relaunch", $abonnement->getTo_relaunch(), \PDO::PARAM_BOOL);
        $q->execute();
    }

    public function updateNbRelanceSinceNoNews(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set nb_relance_since_no_news = :nb_relance_since_no_news where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":nb_relance_since_no_news", $abonnement->getNb_relance_since_no_news());
        $q->execute();
    }

    public function updateRefFormule(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_formule = :ref_formule where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_formule", $abonnement->getRef_formule());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateRefPlan(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_plan = :ref_plan where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_plan", $abonnement->getRef_plan());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateNbMessage(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set nb_message = :nb_message where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":nb_message", $abonnement->getNb_message());
        $q->execute();
    }

    public function updateRefCoupon(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_coupon = :ref_coupon where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->bindValue(":ref_coupon", $abonnement->getRef_coupon());
        $q->execute();
    }

    public function updateFirstProfAssigned(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set first_prof_assigned = :first_prof_assigned where ref_abonnement = :ref_abonnement");
        $q->bindValue(":first_prof_assigned", $abonnement->getFirst_prof_assigned(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateRefCompte(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set ref_compte = :ref_compte where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_compte", $abonnement->getRef_compte());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateDebutEssai(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set debut_essai = :debut_essai where ref_abonnement = :ref_abonnement");
        $q->bindValue(":debut_essai", $abonnement->getDebut_essai());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateFinEssai(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set fin_essai = :fin_essai where ref_abonnement = :ref_abonnement");
        $q->bindValue(":fin_essai", $abonnement->getFin_essai());
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function updateInterruption(\spamtonprof\stp_api\StpAbonnement $abonnement)
    {
        $q = $this->_db->prepare("update stp_abonnement set interruption = :interruption where ref_abonnement = :ref_abonnement");
        $q->bindValue(":interruption", $abonnement->getInterruption(), PDO::PARAM_BOOL);
        $q->bindValue(":ref_abonnement", $abonnement->getRef_abonnement());
        $q->execute();
    }

    public function cast(\spamtonprof\stp_api\StpAbonnement $object)
    {
        return ($object);
    }

    function activate_sub_after_checkout_sucess($subscription_id, $customer_id, $livemode)
    {
        $stripeMg = new \spamtonprof\stp_api\StripeManager(! $livemode);

        $sub = $stripeMg->retrieve_sub($subscription_id);

        $refAbonnement = $sub->metadata['ref_abonnement'];

        $constructor = array(
            "construct" => array(
                'ref_prof',
                'ref_eleve',
                'ref_parent',
                'ref_formule',
                'ref_plan',
                'ref_compte'
            )
        );

        $abonnement = $this->get(array(
            "ref_abonnement" => $refAbonnement
        ), $constructor);

        $eleve = $abonnement->getEleve();
        $proche = $abonnement->getProche();
        $prof = $abonnement->getProf();
        $plan = $abonnement->getPlan();
        $formule = $abonnement->getFormule();
        $compte = $abonnement->getCompte();

        $slack = new \spamtonprof\slack\Slack();

        $slack->sendMessages("abonnement", array(

            "Nouvel abonnement, bien joué la team !!",

            'Email eleve : ' . $eleve->getEmail(),

            'Ref abo stripe : ' . $subscription_id
        ));

        $abonnement->setSubs_Id($subscription_id);
        $this->updateSubsId($abonnement);

        $compteMg = new \spamtonprof\stp_api\StpCompteManager();
        $compte->setStripe_client($customer_id);
        $compteMg->updateStripeClient($compte);

        $abonnement->setRef_statut_abonnement(\spamtonprof\stp_api\StpStatutAbonnementManager::ACTIF);
        $this->updateRefStatutAbonnement($abonnement);

        $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();
        $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
            "ref_abonnement" => $abonnement->getRef_abonnement(),
            "ref_statut_abo" => $abonnement->getRef_statut_abonnement()
        )));

        $smtpMg = new \spamtonprof\stp_api\SmtpServerManager();
        $smtp = $smtpMg->get(array(
            "ref_smtp_server" => $smtpMg::smtp2Go
        ));
        $expeMg = new \spamtonprof\stp_api\StpExpeManager();
        $expe = $expeMg->get("info@spamtonprof.com");

        if ($eleve->hasToSendToParent()) {
            $body_parent = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/abonnement_parent.html");
            $body_parent = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_parent);
            $body_parent = str_replace("[[name_proche]]", ucfirst($eleve->getPrenom()), $body_parent);
            $body_parent = str_replace("[[name]]", ucfirst($proche->getPrenom()), $body_parent);

            $smtp->sendEmail("Félicitations, " . ucfirst($eleve->getPrenom()) . " a compris notre philosophie", $proche->getEmail(), $body_parent, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }

        if ($eleve->hasToSendToEleve()) {
            $body_eleve = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/abonnement_eleve.html");
            $body_eleve = str_replace("[[name]]", ucfirst($eleve->getPrenom()), $body_eleve);
            $body_eleve = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_eleve);
            $smtp->sendEmail("Félicitations, tu as compris notre philosophie", $eleve->getEmail(), $body_eleve, $expe->getEmail(), "Alexandre de SpamTonProf", true);
        }

        // envoi prof
        $body_prof = file_get_contents(ABSPATH . "wp-content/plugins/spamtonprof/emails/abonnement_prof.html");
        $body_prof = str_replace("[[eleve_name]]", ucfirst($eleve->getPrenom()), $body_prof);
        $body_prof = str_replace("[[prof_name]]", ucfirst($prof->getPrenom()), $body_prof);
        $body_prof = str_replace("[[formule]]", $formule->getFormule(), $body_prof);
        $body_prof = str_replace("[[tarif]]", $plan->getTarif(), $body_prof);
        $smtp->sendEmail("Bravo, une semaine d'essai concluante pour " . $eleve->getPrenom() . "! ", $prof->getEmail_stp(), $body_prof, $expe->getEmail(), "Alexandre de SpamTonProf", true);

        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

        $constructor = array(
            "construct" => array(
                'ref_statut_abonnement'
            )
        );

        $algoliaMg->updateAbonnement($abonnement->getRef_abonnement(), $constructor);
    }

    public function construct($constructor)
    {
        $formuleMg = new \spamtonprof\stp_api\StpFormuleManager();
        $profMg = new \spamtonprof\stp_api\StpProfManager();
        $procheMg = new \spamtonprof\stp_api\StpProcheManager();

        $abonnement = $this->cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {
                case "ref_eleve":
                    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
                    $eleve = $eleveMg->get(array(
                        'ref_eleve' => $abonnement->getRef_eleve()
                    ));

                    if (array_key_exists("ref_eleve", $constructor)) {

                        $constructorEleve = $constructor["ref_eleve"];
                        $constructorEleve["objet"] = $eleve;

                        $eleveMg->construct($constructorEleve);
                    }
                    $abonnement->setEleve($eleve);
                    break;

                case "remarquesMatieres":

                    $remarqueInscriptionMg = new \spamtonprof\stp_api\StpRemarqueInscriptionManager();
                    $constructorRmqs = false;

                    if (array_key_exists("remarquesMatieres", $constructor)) {
                        $constructorRmqs = $constructor["remarquesMatieres"];
                    }

                    $rmqs = $remarqueInscriptionMg->getAll(array(
                        "ref_abonnement" => $abonnement->getRef_abonnement()
                    ), $constructorRmqs);

                    $abonnement->setRemarquesMatieres($rmqs);
                    break;

                case "ref_formule":

                    $constructorForumule = false;
                    if (array_key_exists("ref_formule", $constructor)) {

                        $constructorForumule = $constructor["ref_formule"];
                    }

                    $formule = $formuleMg->get(array(
                        'ref_formule' => $abonnement->getRef_formule()
                    ), $constructorForumule);

                    $abonnement->setFormule($formule);
                    break;
                case "ref_prof":
                    if (! is_null($abonnement->getRef_prof())) {
                        $prof = $profMg->get(array(
                            'ref_prof' => $abonnement->getRef_prof()
                        ));

                        if (array_key_exists("ref_prof", $constructor)) {

                            $constructorProf = $constructor["ref_prof"];
                            $constructorProf["objet"] = $prof;

                            $profMg->construct($constructorProf);
                        }
                        $abonnement->setProf($prof);
                    }
                    break;
                case "ref_parent":
                    if (! is_null($abonnement->getRef_proche())) {
                        $procheMg = new \spamtonprof\stp_api\StpProcheManager();
                        $proche = $procheMg->get(array(
                            'ref_proche' => $abonnement->getRef_proche()
                        ));

                        $abonnement->setProche($proche);
                    }
                    break;
                case "ref_plan":
                    $planMg = new \spamtonprof\stp_api\StpPlanManager();
                    $plan = $planMg->get(array(
                        'ref_plan' => $abonnement->getRef_plan()
                    ));

                    $abonnement->setPlan($plan);
                    break;
                case "ref_statut_abonnement":
                    $statutAboMg = new \spamtonprof\stp_api\StpStatutAbonnementManager();
                    $statutAbo = $statutAboMg->get(array(
                        'ref_statut_abonnement' => $abonnement->getRef_statut_abonnement()
                    ));
                    $abonnement->setStatut($statutAbo);
                case "ref_coupon":
                    $couponMg = new \spamtonprof\stp_api\StpCouponManager();
                    if (! is_null($abonnement->getRef_coupon())) {
                        $coupon = $couponMg->get(array(
                            'ref_coupon' => $abonnement->getRef_coupon()
                        ));
                        $abonnement->setCoupon($coupon);
                    }
                    break;
                case "ref_compte":
                    $compteMg = new \spamtonprof\stp_api\StpCompteManager();
                    $compte = $compteMg->get(array(
                        'ref_compte' => $abonnement->getRef_compte()
                    ));

                    $abonnement->setCompte($compte);
                    break;
            }
        }
    }

    public function get($info, $constructor = false)
    {
        $q = null;

        if (array_key_exists("ref_abonnement", $info)) {

            $refAbonnement = $info["ref_abonnement"];
            $q = $this->_db->prepare("select * from stp_abonnement where ref_abonnement =:ref_abonnement");
            $q->bindValue(":ref_abonnement", $refAbonnement);
            $q->execute();
        } else if (array_key_exists("ref_formule", $info) && array_key_exists("ref_eleve", $info)) {

            $refFormule = $info["ref_formule"];
            $refEleve = $info["ref_eleve"];
            $q = $this->_db->prepare("select * from stp_abonnement where ref_formule =:ref_formule and ref_eleve =:ref_eleve");
            $q->bindValue(":ref_formule", $refFormule);
            $q->bindValue(":ref_eleve", $refEleve);
            $q->execute();
        } else if (array_key_exists("subs_id", $info)) {

            $subId = $info["subs_id"];
            $q = $this->_db->prepare("select * from stp_abonnement where subs_id =:subs_id");
            $q->bindValue(":subs_id", $subId);
            $q->execute();
        }

        $data = $q->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);

            if ($constructor) {
                $constructor["objet"] = $abonnement;
                $this->construct($constructor);
            }

            return ($abonnement);
        }
        return (false);
    }

    public function updateAll($info)
    {
        $q = null;

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $q = false;

                $key = $info['key'];

                if ($key == "relaunch_to_false") {

                    $q = $this->_db->prepare("update stp_abonnement set to_relaunch = false");
                    $q->execute();
                    
                    
                }

                if ($key == "trial_sub_not_relaunched_to_relaunch") {

                    $days_since_last_contact = $info['days_since_last_contact'];

                    $q = $this->_db->prepare("
                    update stp_abonnement set to_relaunch = true
                    where (((dernier_contact + interval '" . $days_since_last_contact . " days') <= now()) or dernier_contact is null)
                        and ref_statut_abonnement = 2
                        and ((date_attribution_prof + interval '1 days' <= now()))
                        and (nb_relance_since_no_news is null or nb_relance_since_no_news = 0)
                    ");
                    $q->execute();
                }

                if ($key == "actif_sub_not_relaunched_to_relaunch") {

                    $days_since_last_contact = $info['days_since_last_contact'];

                    $q = $this->_db->prepare("
                    update stp_abonnement set to_relaunch = true
                    where (((dernier_contact + interval '" . $days_since_last_contact . " days') <= now()) or dernier_contact is null)
                        and ref_statut_abonnement = 1
                        and (nb_relance_since_no_news is null or nb_relance_since_no_news = 0)
                    ");

                    $q->execute();
                }

                if ($key == "actif_sub_relaunched_to_relaunch") {

                    $days_since_last_relaunch = $info['days_since_last_relaunch'];

                    $q = $this->_db->prepare("
                    update stp_abonnement set to_relaunch = true
                    where (((relance_date + interval '" . $days_since_last_relaunch . " days') <= now()))
                        and ref_statut_abonnement = 1
                        and (nb_relance_since_no_news > 0)
                    ");

                    $q->execute();
                }

                if ($key == "trial_sub_relaunched_to_relaunch") {

                    $days_since_last_relaunch = $info['days_since_last_relaunch'];

                    $q = $this->_db->prepare("
                    update stp_abonnement set to_relaunch = true
                    where (((relance_date + interval '" . $days_since_last_relaunch . " days') <= now()))
                        and ref_statut_abonnement = 2
                        and (nb_relance_since_no_news > 0)
                    ");

                    $q->execute();
                }
            }
        }
    }

    public function getAll($info, $constructor = false)
    {
        $abonnements = [];
        $q = null;

        if (is_array($info)) {

            if (array_key_exists('key', $info)) {

                $key = $info['key'];

                if ($key == $this::abos_en_cours_dun_prof) {

                    $ref_prof = $info['ref_prof'];

                    $q = $this->_db->prepare('select * from stp_abonnement where ref_statut_abonnement in (1) and ref_prof = :ref_prof');
                    $q->bindValue(":ref_prof", $ref_prof);
                    $q->execute();
                }

                if ($key == $this::all_actif_abos_of_account) {

                    $ref_compte = $info['ref_compte'];

                    $q = $this->_db->prepare('select * from stp_abonnement where ref_compte = :ref_compte and ref_statut_abonnement = 1');
                    $q->bindValue(":ref_compte", $ref_compte);
                    $q->execute();
                }

                if ($key == "abo_to_relaunch") {

                    $q = $this->_db->prepare('select * from stp_abonnement where to_relaunch is true and ref_statut_abonnement in (1,2) order by dernier_contact limit 5 ');
                    $q->execute();
                }
            } else {

                if (array_key_exists("ref_abonnement_lower_with_prof", $info)) {

                    $refAbonnement = $info["ref_abonnement_lower_with_prof"];
                    $q = $this->_db->prepare('select * from stp_abonnement where ref_abonnement >= :ref_abonnement and first_prof_assigned is true');
                    $q->bindValue(":ref_abonnement", $refAbonnement);
                    $q->execute();
                } else if (array_key_exists("ref_eleve", $info) && array_key_exists("ref_prof", $info)) {

                    $refEleve = $info["ref_eleve"];
                    $refProf = $info["ref_prof"];

                    $q = $this->_db->prepare('select * from stp_abonnement where ref_prof = :ref_prof and ref_eleve =:ref_eleve');
                    $q->bindValue(":ref_prof", $refProf);
                    $q->bindValue(":ref_eleve", $refEleve);
                    $q->execute();
                } else if (array_key_exists("ref_statut_abonnement", $info) != false && array_key_exists("ref_compte", $info)) {

                    $refCompte = $info["ref_compte"];
                    $refStatut = $info["ref_statut_abonnement"];
                    $q = $this->_db->prepare('select * from stp_abonnement where ref_compte = :ref_compte and ref_statut_abonnement = :ref_statut_abonnement');
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->bindValue(":ref_statut_abonnement", $refStatut);
                    $q->execute();
                } else if (array_key_exists("ref_statut_abonnement", $info) && array_key_exists("ref_eleve", $info)) {

                    $refStatut = $info["ref_statut_abonnement"];
                    $refEleve = $info["ref_eleve"];

                    $q = $this->_db->prepare('select * from stp_abonnement where ref_eleve = :ref_eleve and ref_statut_abonnement = :ref_statut_abonnement');
                    $q->bindValue(":ref_eleve", $refEleve);
                    $q->bindValue(":ref_statut_abonnement", $refStatut);
                    $q->execute();
                } else if (array_key_exists("ref_eleve", $info)) {

                    $refEleve = $info["ref_eleve"];
                    $q = $this->_db->prepare('select * from stp_abonnement where ref_eleve = :ref_eleve');
                    $q->bindValue(":ref_eleve", $refEleve);
                    $q->execute();
                } else if (array_key_exists("ref_proche", $info)) {

                    $refProche = $info["ref_proche"];
                    $q = $this->_db->prepare('select * from stp_abonnement where ref_proche = :ref_proche');
                    $q->bindValue(":ref_proche", $refProche);
                    $q->execute();
                } else if (array_key_exists("ref_coupon", $info) && array_key_exists("ref_compte", $info)) {

                    $refCoupon = $info["ref_coupon"];
                    $refCompte = $info["ref_compte"];
                    $q = $this->_db->prepare('select * from stp_abonnement where ref_coupon = :ref_coupon and ref_compte = :ref_compte');
                    $q->bindValue(":ref_coupon", $refCoupon);
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->execute();
                } else if (array_key_exists("ref_compte", $info)) {

                    $refCompte = $info["ref_compte"];
                    $q = $this->_db->prepare('select * from stp_abonnement where ref_compte = :ref_compte');
                    $q->bindValue(":ref_compte", $refCompte);
                    $q->execute();
                } else if (array_search("abo_vivant", $info, true) !== false && array_key_exists('offset', $info) && array_key_exists('ref_prof', $info) && array_key_exists('limit', $info)) {

                    $offset = $info['offset'];
                    $ref_prof = $info['ref_prof'];
                    $limit = $info['limit'];

                    $q = $this->_db->prepare('select * from stp_abonnement 
                    where (ref_statut_abonnement = 1 or (ref_statut_abonnement = 2 and extract(day from NOW() - date_creation)<=10))
                        and ref_prof = :ref_prof   
                    limit :limit offset :offset');
                    $q->bindValue(':offset', $offset);
                    $q->bindValue(':limit', $limit);
                    $q->bindValue(':ref_prof', $ref_prof);
                    $q->execute();
                } else if (array_key_exists("ref_prof", $info)) {

                    $refProf = $info["ref_prof"];

                    $q = $this->_db->prepare('select * from stp_abonnement where ref_prof = :ref_prof');
                    $q->bindValue(":ref_prof", $refProf);
                    $q->execute();
                } else if (array_key_exists("email", $info)) {

                    $email = $info["email"];

                    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
                    $procheMg = new \spamtonprof\stp_api\StpProcheManager();

                    $proches = $procheMg->getAll(array(
                        "email" => $email
                    ));

                    $eleves = $eleveMg->getAll(array(
                        "email" => $email
                    ));

                    $refEleves = extractAttribute($eleves, "ref_eleve");

                    $refProches = extractAttribute($proches, "ref_proche");

                    $refEleves = toPgArray($refEleves, true);
                    $refProches = toPgArray($refProches, true);

                    $q = $this->_db->prepare('select * from stp_abonnement where ref_proche in ' . $refProches . ' or ref_eleve in ' . $refEleves);
                    $q->execute();
                } else if (array_key_exists("telephones", $info) && array_key_exists("teleprospection", $info) && array_key_exists("remarques", $info)) {

                    $nums = $info["telephones"];
                    $tele = $info["teleprospection"];
                    $remarques = $info["remarques"];

                    $eleveMg = new \spamtonprof\stp_api\StpEleveManager();
                    $procheMg = new \spamtonprof\stp_api\StpProcheManager();

                    $eleves = $eleveMg->getAll(array(
                        "telephones" => $nums
                    ));
                    $proches = $procheMg->getAll(array(
                        "telephones" => $nums
                    ));

                    $refEleves = [];
                    $refProches = [];

                    foreach ($eleves as $eleve) {
                        $refEleves[] = $eleve->getRef_eleve();
                    }

                    foreach ($proches as $proche) {
                        $refProches[] = $proche->getRef_proche();
                    }
                    $refProches = toPgArray($refProches, true);
                    $refEleves = toPgArray($refEleves, true);

                    $q = $this->_db->prepare('select * from stp_abonnement where ref_proche in ' . $refProches . ' or ref_eleve in ' . $refEleves . ' 
                    or teleprospection = :teleprospection 
                    or lower(remarque_inscription) like unaccent(:remarques) order by ref_proche,date_creation');
                    $q->bindValue(":teleprospection", $tele);
                    $q->bindValue(":remarques", "%" . $remarques . "%");
                    $q->execute();
                } else if (array_key_exists("ref_abonnements", $info)) {

                    $refAbos = $info["ref_abonnements"];
                    $refAbos = toPgArray($refAbos, true);
                    $q = $this->_db->prepare('select * from stp_abonnement where ref_abonnement in ' . $refAbos);
                    $q->execute();
                } else if (array_key_exists("limit", $info) && array_search("all", $info, true) !== false && array_key_exists("offset", $info)) {

                    $offset = $info["offset"];
                    $limit = $info["limit"];

                    $q = $this->_db->prepare('select * from stp_abonnement limit :limit offset :offset');
                    $q->bindValue(':offset', $offset);
                    $q->bindValue(':limit', $limit);
                    $q->execute();
                } else if (array_key_exists("ref_statut_abonnement", $info) && array_search("nb_inactif_day", $info, true) !== false) {

                    $ref_statut_abonnement = $info["ref_statut_abonnement"];
                    $nb_inactif_day = $info["nb_inactif_day"];

                    $q = $this->_db->prepare('select extract(day from now() - dernier_contact)  from stp_abonnement 
	               where extract(day from now() - dernier_contact) > :nb_inactif_day and ref_statut_abonnement = :ref_statut_abonnement');
                    $q->bindValue(':ref_statut_abonnement', $ref_statut_abonnement);
                    $q->bindValue(':nb_inactif_day', $nb_inactif_day);
                    $q->execute();
                } else if (array_key_exists("ref_statut_abonnement", $info) && array_search("nb_inactif_day", $info, true) !== false && array_key_exists("age", $info)) {

                    $ref_statut_abonnement = $info["ref_statut_abonnement"];
                    $nb_inactif_day = $info["nb_inactif_day"];
                    $age = $info["age"];

                    $q = $this->_db->prepare('select extract(day from now() - dernier_contact)  from stp_abonnement
	               where extract(day from now() - dernier_contact) < :nb_inactif_day 
                    and ref_statut_abonnement = :ref_statut_abonnement
                    and extract(day from now() - date_creation) < :age');
                    $q->bindValue(':ref_statut_abonnement', $ref_statut_abonnement);
                    $q->bindValue(':nb_inactif_day', $nb_inactif_day);
                    $q->execute();
                } else if (array_key_exists("ref_statut_abonnement", $info) && array_key_exists("nb_inactif_day", $info) && array_key_exists("limit", $info) && array_key_exists("days_since_relance", $info)) {

                    $ref_statut_abonnement = $info["ref_statut_abonnement"];
                    $days_since_relance = $info["days_since_relance"];
                    $limit = $info["limit"];
                    $nb_inactif_day = $info["nb_inactif_day"];

                    $q = $this->_db->prepare("select *  from stp_abonnement
	               where (extract(day from now() - dernier_contact) > :nb_inactif_day or dernier_contact is null) and ref_statut_abonnement = :ref_statut_abonnement
                        and (relance_date is null or now() > relance_date + interval '" . $days_since_relance . " days')
                        limit :limit");
                    $q->bindValue(':ref_statut_abonnement', $ref_statut_abonnement);
                    $q->bindValue(':nb_inactif_day', $nb_inactif_day);
                    $q->bindValue(':limit', $limit);
                    $q->execute();
                } else if (array_key_exists("ref_interruption", $info)) {

                    $ref_interruption = $info["ref_interruption"];

                    $q = $this->_db->prepare('select * from stp_abonnement
                    where ref_abonnement in (
                        select ref_abonnement from stp_interruption where ref_interruption > :ref_interruption
                    )');
                    $q->bindValue(':ref_interruption', $ref_interruption);
                    $q->execute();
                } else if (array_search("get_trial_account_to_desactivate", $info, true) !== false && array_key_exists("limit", $info)) { // compte en essai dont l'essai est termine et sans message depuis 5 jours

                    $limit = $info["limit"];

                    $q = $this->_db->prepare("select * from stp_abonnement
	               where fin_essai is not null and now() > fin_essai + interval '15' day
				   	and ref_statut_abonnement = 2  
                    and first_prof_assigned = true
                        limit :limit");
                    $q->bindValue(":limit", $limit);
                    $q->execute();
                } else if (array_search("trial_abo_to_relance", $info, true) !== false && array_key_exists("limit", $info)) {

                    $limit = $info["limit"];

                    $q = $this->_db->prepare("
                select * from stp_abonnement where ref_statut_abonnement = 2 
                    and date_attribution_prof + interval '2 days' <= now()
                    and (dernier_contact is null or (dernier_contact + interval '2 days' <= now()))
                    and nb_message <= 2
                    and (relance_date is null  or (relance_date + interval '3 days' <= now()))
                order by date_attribution_prof desc
                limit :limit");

                    $q->bindValue(":limit", $limit);

                    $q->execute();
                } else if (array_key_exists("ref_abos", $info)) {

                    $ref_abos = $info["ref_abos"];

                    $ref_abos = toPgArray($ref_abos, true);

                    $q = $this->_db->prepare("select * from stp_abonnement where ref_abonnement in " . $ref_abos);

                    $q->execute();
                } else if (array_key_exists("with_statut", $info) && array_key_exists("limit", $info)) {

                    $ref_statut_abonnement = $info["with_statut"];
                    $limit = $info["limit"];

                    $q = $this->_db->prepare("select *  from stp_abonnement
	               where ref_statut_abonnement = :ref_statut_abonnement
                        limit :limit");
                    $q->bindValue(':ref_statut_abonnement', $ref_statut_abonnement);
                    $q->bindValue(':limit', $limit);
                    $q->execute();
                }
            }
        }

        if ($info == "no_messages") {
            $q = $this->_db->prepare('select * from stp_abonnement where nb_message = 0');
            $q->execute();
        }

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);

            if ($constructor) {
                $constructor["objet"] = $abonnement;
                $this->construct($constructor);
            }
            $abonnements[] = $abonnement;
        }
        return ($abonnements);
    }

    // pour desactier les comptes tests . $email peut valoir yopla ou test pex ( tout depend de la convenation de nommage des emails test
    function desactiveTestAccount($email)
    {
        $abonnements = $this->getAll(array(
            "email" => $email
        ));

        foreach ($abonnements as $abonnement) {
            $abonnement->setRef_statut_abonnement($abonnement::DESACTIVE);
            $abonnement->setDate_attribution_prof(null);
            $this->updateRefStatutAbonnement($abonnement);
            $this->updateDateAttributionProf($abonnement);
        }
    }

    function updateProf($refAbo, $mailProfStp, $testMode = true)
    {
        $profMg = new \spamtonprof\stp_api\StpProfManager();

        $prof = $profMg->get(array(
            "email_stp" => $mailProfStp
        ));

        $abo = $this->get(array(
            "ref_abonnement" => $refAbo
        ));
        $abo->setRef_prof($prof->getRef_prof());
        $this->updateRefProf($abo);

        if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ESSAI) {

            $gr = new \GetResponse();
            $gr->updateTrialList($refAbo);
        } else if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ACTIF) {

            $subId = $abo->getSubs_Id();

            $stripe = new \spamtonprof\stp_api\StripeManager($testMode);

            $stripe->updateStripeProfId($subId, $prof->getStripe_id());
        }

        // mise a jour algolia
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

        $constructor = array(
            "construct" => array(
                'ref_prof'
            )
        );

        $algoliaMg->updateAbonnement($abo->getRef_abonnement(), $constructor);
    }

    // champs de $fields : prenom, nom, ref_niveau
    function updateEleve($refAbo, array $fields)
    {
        $eleveMg = new \spamtonprof\stp_api\StpEleveManager();

        $constructor = array(
            "construct" => array(
                'ref_eleve'
            ),
            "ref_eleve" => array(
                "construct" => array(
                    'ref_niveau'
                )
            )
        );

        $abo = $this->get(array(
            "ref_abonnement" => $refAbo
        ), $constructor);

        $eleve = $abo->getEleve();
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

        if (array_key_exists("prenom", $fields)) {
            $eleve->setPrenom($fields["prenom"]);
            $eleveMg->updatePrenom($eleve);
        }

        if (array_key_exists("nom", $fields)) {
            $eleve->setNom($fields["nom"]);
            $eleveMg->updateNom($eleve);
        }

        if (array_key_exists("ref_niveau", $fields)) {
            $eleve->setRef_niveau($fields["ref_niveau"]);
            $eleveMg->updateRefNiveau($eleve);
        }

        if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ESSAI) {
            $gr = new \GetResponse();
            $gr->updateTrialList($refAbo);
        }

        // mise a jour algolia
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
        $algoliaMg->updateAbonnement($abo->getRef_abonnement(), $constructor);
    }

    // pour redemarrer un abonnement qui a ete arrete (startDate vaut now ou une date)
    function restart(int $refAbo, bool $testMode = true, bool $in_trial = false, $startDate = 'now', $add_getreponse = false, $facturer = true)
    {
        if ($startDate != 'now') {
            $startDate = \DateTime::createFromFormat('j/m/Y', $startDate);
            $startDate = $startDate->getTimestamp();
        }

        // mise a jour dans la bdd
        $constructor = array(
            "construct" => array(
                'ref_parent',
                'ref_plan',
                'ref_prof',
                'ref_compte',
                'ref_eleve'
            )
        );
        $abo = $this->get(array(
            'ref_abonnement' => $refAbo
        ), $constructor);

        $statut = $abo::ACTIF;
        if ($in_trial) {
            $statut = $abo::ESSAI;
        }

        $abo->setRef_statut_abonnement($statut);
        $this->updateRefStatutAbonnement($abo);

        // mise a jour dans stripe

        if (! $in_trial && $facturer) {

            $planStripeId = $abo->getPlan()->getRef_plan_stripe();
            $stripeProfId = $abo->getProf()->getStripe_id();
            if ($testMode) {
                $planStripeId = $abo->getPlan()->getRef_plan_stripe_test();
                $stripeProfId = $abo->getProf()->getStripe_id_test();
            }

            $stripe = new \spamtonprof\stp_api\StripeManager($testMode);

            $invoice_email = $abo->getEleve()->getEmail();
            $proche = $abo->getProche();

            if ($proche) {
                $invoice_email = $proche->getEmail();
            }

            $ret = $stripe->addConnectSubscription($invoice_email, false, $abo->getRef_compte(), $planStripeId, $stripeProfId, $abo->getRef_abonnement(), $abo->getCompte(), $startDate);

            $abo->setSubs_Id($ret["subId"]);
            $this->updateSubsId($abo);
        } else {

            $fin_essai = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
            $fin_essai->sub(new \DateInterval('P10D'));
            $abo->setFin_essai($fin_essai->format(PG_DATE_FORMAT));
            $this->updateFinEssai($abo);

            if ($add_getreponse) {
                $now = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
                $now->sub(new \DateInterval('PT1H'));

                $abo->setFirst_prof_assigned(false);
                $abo->setDate_attribution_prof($now);

                $this->updateDateAttributionProf($abo);
                $this->updateFirstProfAssigned($abo);
            }
        }

        // mise a jour du statut d'abonnement dans algolia
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

        $constructor = array(
            "construct" => array(
                'ref_statut_abonnement'
            )
        );

        $algoliaMg->updateAbonnement($refAbo, $constructor);
    }

    // stop essai
    function stopEssai($refAbo)
    {
        $logAboMg = new \spamtonprof\stp_api\StpLogAbonnementManager();

        $abo = $this->get(array(
            'ref_abonnement' => $refAbo
        ));

        $abo->setRef_statut_abonnement($abo::TERMINE);

        $logAboMg->add(new \spamtonprof\stp_api\StpLogAbonnement(array(
            "ref_abonnement" => $abo->getRef_abonnement(),
            "ref_statut_abo" => $abo->getRef_statut_abonnement()
        )));
        $this->updateRefStatutAbonnement($abo);

        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();

        $constructor = array(
            "construct" => array(
                'ref_statut_abonnement'
            )
        );

        $algoliaMg->updateAbonnement($refAbo, $constructor);
    }

    // mise a jour du plan de paiement et de la formule
    function updateFormule($refAbo, int $refFormule, bool $testMode = true, $defaut_plan = true)
    {
        $constructor = array(
            "construct" => array(
                'ref_formule',
                'ref_plan'
            )
        );

        $abo = $this->get(array(
            "ref_abonnement" => $refAbo
        ), $constructor);

        // on recupere le nouveau plan
        $planMg = new \spamtonprof\stp_api\StpPlanManager();

        if ($defaut_plan === true) {

            $plan = $planMg->getDefault(array(
                "ref_formule" => $refFormule
            ));
        } else {

            $plan = $planMg->get(array(
                "ref_formule" => $refFormule,
                'nom' => $defaut_plan
            ));
        }

        // mise a jour du plan et de la formule dans la base

        $abo->setRef_plan($plan->getRef_plan());
        $this->updateRefPlan($abo);
        $abo->setRef_formule($refFormule);
        $this->updateRefFormule($abo);

        // traitement specifique aux status
        if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ESSAI && $abo->getRef_prof()) {
            $gr = new \GetResponse();
            $gr->updateTrialList($refAbo);
        } else if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ACTIF) {
            $stripe = new \spamtonprof\stp_api\StripeManager($testMode);
            $stripe->updateSubscriptionPlan($abo->getSubs_Id(), $plan);
        }

        // mise a jour algolia
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
        $algoliaMg->updateAbonnement($abo->getRef_abonnement(), $constructor);
    }

    // pour avoir les conversions de Amina a partir d'un tableau de numero de telephone non formate
    function getAnimaSubscription($nums)
    {
        $constructor = array(
            "construct" => array(
                'ref_parent',
                'ref_eleve'
            )
        );

        $abonnements = $this->getAll(array(
            "telephones" => $nums,
            "teleprospection" => "oui",
            "remarques" => "arnaud"
        ), $constructor);
        return ($abonnements);
    }

    // pour mettre a jour l'email d'un parent
    function updateEmailEleve($email, $refAbo)
    {

        // on recupere l'abonnement
        $constructor = array(
            "construct" => array(
                'ref_eleve',
                'ref_parent',
                'ref_statut_abonnement',
                'ref_formule',
                'ref_prof'
            )
        );

        $eleveMg = new \spamtonprof\stp_api\StpEleveManager();

        $abo = $this->get(array(
            "ref_abonnement" => $refAbo
        ), $constructor);

        $eleve = $abo->getEleve();
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

        $parent = $abo->getProche();
        $parent = \spamtonprof\stp_api\StpProche::cast($parent);

        $formule = $abo->getFormule();
        $prof = $abo->getProf();

        if ($eleve->getEmail() == $email) {

            exit(0);
        }

        // maj getresponse - remove list essai + ajout liste essai si essai

        if ($abo->getFirst_prof_assigned() && $abo->getRef_statut_abonnement() == $abo::ESSAI) {

            $gr = new \GetResponse();

            $contact = $gr->getContactInList($parent->getEmail(), "stp_eleve_essai");

            $dayOfCycle = 0;
            if ($contact) {
                $dayOfCycle = $contact->dayOfCycle;
                $gr->deleteContact($contact->contactId);
            }
            $parent->setEmail($email);
            $gr->addEleveInTrialSequence($eleve, $prof, $formule, $dayOfCycle);
        }

        // mise a jour de l'email dans la base
        $eleve->setEmail($email);
        $eleveMg->updateEmail($eleve);

        // update index
        $algolia = new \spamtonprof\stp_api\AlgoliaManager();
        $algolia->updateAbonnement($refAbo, $constructor);

        // mise à jour compte wp
        $ret = wp_update_user(array(
            'ID' => $eleve->getRef_compte_wp(),
            'user_email' => $email
        ));
        prettyPrint($ret);
    }

    // pour mettre a jour l'email d'un parent
    function updateEmailParent($email, $refAbo)
    {

        // on recupere l'abonnement
        $constructor = array(
            "construct" => array(
                'ref_eleve',
                'ref_parent',
                'ref_statut_abonnement',
                'ref_formule',
                'ref_prof'
            )
        );

        $procheMg = new \spamtonprof\stp_api\StpProcheManager();

        $abo = $this->get(array(
            "ref_abonnement" => $refAbo
        ), $constructor);

        $eleve = $abo->getEleve();
        $eleve = \spamtonprof\stp_api\StpEleve::cast($eleve);

        $parent = $abo->getProche();
        $parent = \spamtonprof\stp_api\StpProche::cast($parent);

        $formule = $abo->getFormule();
        $prof = $abo->getProf();

        if ($parent->getEmail() == $email) {

            exit(0);
        }

        // maj getresponse - remove list essai + ajout liste essai si essai

        if ($abo->getFirst_prof_assigned() && $abo->getRef_statut_abonnement() == $abo::ESSAI) {

            $gr = new \GetResponse();

            $end_seq = "";
            if ($eleve->getSeq_email_parent_essai() == "2") {
                $end_seq = "_2";
            }
            $contact = $gr->getContactInList($parent->getEmail(), "stp_parent_essai" . $end_seq);

            $dayOfCycle = 0;
            if ($contact) {
                $dayOfCycle = $contact->dayOfCycle;
                $gr->deleteContact($contact->contactId);
            }
            $parent->setEmail($email);
            $gr->addParentInTrialSequence1($eleve, $prof, $formule, $parent, $dayOfCycle);
        }

        // mise a jour de l'email dans la base
        $parent->setEmail($email);

        $procheMg->updateEmail($parent);

        // update index
        $algolia = new \spamtonprof\stp_api\AlgoliaManager();
        $algolia->updateAbonnement($refAbo, $constructor);
    }
}
