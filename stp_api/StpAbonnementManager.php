<?php
namespace spamtonprof\stp_api;

use PDO;

class StpAbonnementManager
{

    private $_db, $eleveMg;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpAbonnement $StpAbonnement)
    {
        $q = $this->_db->prepare('insert into stp_abonnement(ref_eleve, ref_formule, ref_statut_abonnement, date_creation, remarque_inscription, ref_plan) values( :ref_eleve,:ref_formule,:ref_statut_abonnement,:date_creation,:remarque_inscription,:ref_plan)');
        $q->bindValue(':ref_eleve', $StpAbonnement->getRef_eleve());
        $q->bindValue(':ref_formule', $StpAbonnement->getRef_formule());
        $q->bindValue(':ref_statut_abonnement', $StpAbonnement->getRef_statut_abonnement());
        $q->bindValue(':date_creation', $StpAbonnement->getDate_creation()
            ->format(PG_DATETIME_FORMAT));
        $q->bindValue(':remarque_inscription', $StpAbonnement->getRemarque_inscription());
        $q->bindValue(':ref_plan', $StpAbonnement->getRef_plan());
        $q->execute();

        $StpAbonnement->setRef_abonnement($this->_db->lastInsertId());

        return ($StpAbonnement);
    }

    /*
     * pour remonter les abonnements sans prof dans le dashboard choisir prof
     */
    public function getAbonnementsSansProf()
    {
        $abonnements = [];

        $q = $this->_db->prepare("select * from stp_abonnement where ref_prof is null and ref_statut_abonnement not in (4) order by date_creation ");

        $q->execute();

        while ($data = $q->fetch(PDO::FETCH_ASSOC)) {

            $abonnement = new \spamtonprof\stp_api\StpAbonnement($data);

            $this->construct(array(
                "objet" => $abonnement,
                "construct" => array(
                    'ref_eleve',
                    'ref_formule',
                    'ref_prof'
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
     * pour retourner les abonnements dont la période d'essai est terminé
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
     * pour récupérer le nombre de message des abonnements durant les 7 derniers jours
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
     * la date de debut doit être au minimum celle d'aujourdh'ui si avant 20h ( le cron tourne à 20h )
     */
    function interrupt($debut, $fin, $refAbo, $prorate = false, $prolongation = false)
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
     * pour remettre à zéro messages tous les abonnements
     *
     */
    public function resetNbMessage()
    {
        $q = $this->_db->prepare("update stp_abonnement set nb_message = 0 where nb_message != 0 OR nb_message is null");
        $q->execute();
    }

    // pour remonter les abonnements qui viennent de se voir attribuer un prof pour la première fois après l'inscription
    public function getHasNotFirstProfAssignement()
    {
        $abonnements = [];

        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));

        $q = $this->_db->prepare("select * from stp_abonnement where first_prof_assigned = false and date_attribution_prof <= :now");
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
                    $prof = $profMg->get(array(
                        'ref_prof' => $abonnement->getRef_prof()
                    ));

                    $abonnement->setProf($prof);
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

    public function getAll($info, $constructor = false)
    {
        $abonnements = [];
        $q = null;

        if (is_array($info)) {
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
            } else if (array_key_exists("ref_statut_abonnement", $info) && array_key_exists("ref_compte", $info)) {

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
            } else if (array_key_exists("ref_compte", $info)) {

                $refCompte = $info["ref_compte"];
                $q = $this->_db->prepare('select * from stp_abonnement where ref_compte = :ref_compte');
                $q->bindValue(":ref_compte", $refCompte);
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
                    "email" => "yopla"
                ));
                $eleves = $eleveMg->getAll(array(
                    "email" => "yopla"
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
            }
        }

        if ($info == "all") {
            $q = $this->_db->prepare('select * from stp_abonnement');
            $q->execute();
        } else if ($info == "no_messages") {
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

    // pour désactier les comptes tests . $email peut valoir yopla ou test pex ( tout dépend de la convenation de nommage des emails test
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

        // mise à jour algolia
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

        // mise à jour algolia
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
        $algoliaMg->updateAbonnement($abo->getRef_abonnement(), $constructor);
    }

    // pour redémarrer un abonnement qui a été arrêté (startDate vaut now ou une date)
    function restart(int $refAbo, $startDate, bool $testMode = true)
    {
        if ($startDate != 'now') {
            $startDate = \DateTime::createFromFormat('j/m/Y', $startDate);
            $startDate = $startDate->getTimestamp();
        }

        // mise à jour dans la bdd
        $constructor = array(
            "construct" => array(
                'ref_parent',
                'ref_plan',
                'ref_prof',
                'ref_compte'
            )
        );
        $abo = $this->get(array(
            'ref_abonnement' => $refAbo
        ), $constructor);

        $abo->setRef_statut_abonnement($abo::ACTIF);
        $this->updateRefStatutAbonnement($abo);

        // mise à jour dans stripe

        $planStripeId = $abo->getPlan()->getRef_plan_stripe();
        $stripeProfId = $abo->getProf()->getStripe_id();
        if ($testMode) {
            $planStripeId = $abo->getPlan()->getRef_plan_stripe_test();
            $stripeProfId = $abo->getProf()->getStripe_id_test();
        }

        $stripe = new \spamtonprof\stp_api\StripeManager($testMode);
        $ret = $stripe->addConnectSubscription($abo->getProche()
            ->getEmail(), false, $abo->getRef_compte(), $planStripeId, $stripeProfId, $abo->getRef_abonnement(), $abo->getCompte(), $startDate);

        $abo->setSubs_Id($ret["subId"]);
        $this->updateSubsId($abo);
    }

    // mise à jour du plan de paiement et de la formule
    function updateFormule($refAbo, int $refFormule, bool $testMode = true)
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

        // on récupère le nouveau plan
        $planMg = new \spamtonprof\stp_api\StpPlanManager();

        $plan = $planMg->getDefault(array(
            "ref_formule" => $refFormule
        ));

        // mise à jour du plan et de la formule dans la base

        $abo->setRef_plan($plan->getRef_plan());
        $this->updateRefPlan($abo);
        $abo->setRef_formule($refFormule);
        $this->updateRefFormule($abo);

        // traitement spécifique aux status
        if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ESSAI) {
            $gr = new \GetResponse();
            $gr->updateTrialList($refAbo);
        } else if ($abo->getRef_statut_abonnement() == \spamtonprof\stp_api\StpAbonnement::ACTIF) {
            $stripe = new \spamtonprof\stp_api\StripeManager($testMode);
            $stripe->updateSubscriptionPlan($abo->getSubs_Id(), $plan);
        }

        // mise à jour algolia
        $algoliaMg = new \spamtonprof\stp_api\AlgoliaManager();
        $algoliaMg->updateAbonnement($abo->getRef_abonnement(), $constructor);
    }

    // pour avoir les conversions de Amina à partir d'un tableau de numéro de téléphone non formaté
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
            "remarques" => "chloe "
        ), $constructor);
        return ($abonnements);
    }

    // pour mettre à jour l'email d'un parent
    function updateEmailParent($email, $refAbo)
    {

        // on récupère l'abonnement
        $constructor = array(
            "construct" => array(
                'ref_eleve',
                'ref_parent',
                'ref_statut_abonnement',
                'ref_formule',
                'ref_prof'
            )
        );

        $abonnementMg = new \spamtonprof\stp_api\StpAbonnementManager();
        $procheMg = new \spamtonprof\stp_api\StpProcheManager();

        $abo = $abonnementMg->get(array(
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

        // mise à jour de l'email dans la base
        $parent->setEmail($email);

        $procheMg->updateEmail($parent);

        // update index
        $algolia = new \spamtonprof\stp_api\AlgoliaManager();
        $algolia->updateAbonnement($refAbo, $constructor);
    }
}
