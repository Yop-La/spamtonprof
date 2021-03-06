<?php
namespace spamtonprof\stp_api;

class StpProfManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpProf $StpProf)
    {
        $q = $this->_db->prepare('insert into stp_prof(email_perso, prenom, nom, telephone, onboarding_step, date_naissance, sexe, processing_date) 
            values( :email_perso,:prenom,:nom,:telephone, :onboarding_step, :date_naissance, :sexe, :processing_date)');
        $q->bindValue(':email_perso', $StpProf->getEmail_perso());
        $q->bindValue(':prenom', $StpProf->getPrenom());
        $q->bindValue(':nom', $StpProf->getNom());
        $q->bindValue(':telephone', $StpProf->getTelephone());
        $q->bindValue(':onboarding_step', $StpProf->getOnboarding_step());
        $q->bindValue(':date_naissance', $StpProf->getDate_naissance()
            ->format(PG_DATE_FORMAT));
        $q->bindValue(':sexe', $StpProf->getSexe());
        $q->bindValue(':processing_date', $StpProf->getProcessing_date()
            ->format(PG_DATETIME_FORMAT));
        $q->execute();

        $StpProf->setRef_prof($this->_db->lastInsertId());

        return ($StpProf);
    }

    public function get($info)
    {
        $q = null;

        if (array_key_exists('ref_gmail_account', $info)) {

            $refGmailAccount = $info['ref_gmail_account'];

            $q = $this->_db->prepare('select * from stp_prof where ref_gmail_account = :ref_gmail_account');

            $q->bindValue(':ref_gmail_account', $refGmailAccount);
        }

        if (array_key_exists('email_perso', $info)) {

            $emailPerso = $info['email_perso'];

            $q = $this->_db->prepare('select * from stp_prof where lower(email_perso) like lower(:email_perso)');

            $q->bindValue(':email_perso', $emailPerso);
        }

        if (array_key_exists('email_stp', $info)) {

            $emailStp = $info['email_stp'];

            $q = $this->_db->prepare('select * from stp_prof where lower(email_stp) like lower(:email_stp)');

            $q->bindValue(':email_stp', $emailStp);
        }

        if (array_key_exists('user_id_wp', $info)) {

            $userId = $info['user_id_wp'];

            $q = $this->_db->prepare('select * from stp_prof where lower(user_id_wp) like lower(:user_id_wp)');
            $q->bindValue(':user_id_wp', $userId);
        }

        if (array_key_exists('ref_prof', $info)) {

            $refProf = $info['ref_prof'];

            $q = $this->_db->prepare('select * from stp_prof where ref_prof = :ref_prof');

            $q->bindValue(':ref_prof', $refProf);
        }

        if (array_key_exists('stripe_id', $info)) {

            $stripe_id = $info['stripe_id'];

            $q = $this->_db->prepare('select * from stp_prof where stripe_id = :stripe_id');

            $q->bindValue(':stripe_id', $stripe_id);
        }

        if (! is_null($q)) {

            $q->execute();
            $data = $q->fetch(\PDO::FETCH_ASSOC);

            if ($data) {
                return (new \spamtonprof\stp_api\StpProf($data));
            } else {
                return (false);
            }
        } else {
            return (false);
        }
    }

    public function getAll($info = null, $constructor = false)
    {
        $profs = [];
        $q = null;

        if (is_array($info)) {

            if (array_key_exists("inbox_ready", $info)) {

                $inboxReady = $info["inbox_ready"];

                $q = $this->_db->prepare('select * from stp_prof where inbox_ready = :inbox_ready order by ref_prof');

                $q->bindValue(":inbox_ready", $inboxReady, \PDO::PARAM_BOOL);

                $q->execute();
            }
        } else {

            $q = $this->_db->prepare('select * from stp_prof ');

            $q->execute();
        }

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $prof = new \spamtonprof\stp_api\StpProf($data);
            ;

            if ($constructor) {
                $constructor["objet"] = $prof;
                $this->construct($constructor);
            }

            $profs[] = $prof;
        }
        return ($profs);
    }

    public function construct($constructor)
    {
        $gmailAccMg = new \spamtonprof\stp_api\StpGmailAccountManager();

        $prof = $this->cast($constructor["objet"]);

        $constructOrders = $constructor["construct"];

        foreach ($constructOrders as $constructOrder) {

            switch ($constructOrder) {

                case "ref_gmail_account":
                    $gmailAcc = $gmailAccMg->get($prof->getRef_gmail_account());

                    $prof->setGmailAcc($gmailAcc);
                    break;
            }
        }
    }

    public function getNextInboxToProcess()
    {
        $q = $this->_db->prepare('select * from stp_prof where inbox_ready = true order by processing_date limit 1');

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {

            return (new \spamtonprof\stp_api\StpProf($data));
        } else {
            return (false);
        }
    }

    public function updateUserIdWp(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = null;

        $q = $this->_db->prepare('update stp_prof set user_id_wp = :user_id_wp where ref_prof = :ref_prof');
        $q->bindValue(':user_id_wp', $prof->getUser_id_wp());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateProcessingDate(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set processing_date = :processing_date where ref_prof = :ref_prof');

        $q->bindValue(':processing_date', $prof->getProcessing_date());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateOnboarding(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set onboarding = :onboarding where ref_prof = :ref_prof');

        $q->bindValue(':onboarding', $prof->getOnboarding(), \PDO::PARAM_BOOL);

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateCusIdTest(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set cus_test = :cus_test where ref_prof = :ref_prof');

        $q->bindValue(':cus_test', $prof->getCus_test());
        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateCusId(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set cus = :cus where ref_prof = :ref_prof');

        $q->bindValue(':cus', $prof->getCus());
        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateCustomer(\spamtonprof\stp_api\StpProf $prof, $test_mode)
    {
        if ($test_mode) {
            $this->updateCusIdTest($prof);
        }

        $this->updateCusId($prof);
    }

    public function updateStripeId(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set stripe_id = :stripe_id where ref_prof = :ref_prof');

        $q->bindValue(':stripe_id', $prof->getStripe_id());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateStripeIdTest(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set stripe_id_test = :stripe_id_test where ref_prof = :ref_prof');

        $q->bindValue(':stripe_id_test', $prof->getStripe_id_test());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateAdresse(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set adresse = :adresse where ref_prof = :ref_prof');

        $q->bindValue(':adresse', $prof->getAdresse());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateVille(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set ville = :ville where ref_prof = :ref_prof');

        $q->bindValue(':ville', $prof->getVille());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateCodePostal(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set code_postal = :code_postal where ref_prof = :ref_prof');

        $q->bindValue(':code_postal', $prof->getCode_postal());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateHistoryId(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set history_id = :history_id where ref_prof = :ref_prof');

        $q->bindValue(':history_id', $prof->getRef_prof());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updatePays(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set pays = :pays where ref_prof = :ref_prof');

        $q->bindValue(':pays', $prof->getPays());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateOnboarding_step(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set onboarding_step = :onboarding_step where ref_prof = :ref_prof');

        $q->bindValue(':onboarding_step', $prof->getOnboarding_step());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function updateIban(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare('update stp_prof set iban = :iban where ref_prof = :ref_prof');

        $q->bindValue(':iban', $prof->getIban());

        $q->bindValue(':ref_prof', $prof->getRef_prof());

        $q->execute();

        return ($prof);
    }

    public function cast(\spamtonprof\stp_api\StpProf $object)
    {
        return ($object);
    }

    public function updateGrId(\spamtonprof\stp_api\StpProf $prof)
    {
        $q = $this->_db->prepare("update stp_prof set gr_id = :gr_id where ref_prof = :ref_prof");
        $q->bindValue(":ref_prof", $prof->getRef_prof());
        $q->bindValue(":gr_id", $prof->getGr_id());
        $q->execute();
    }

    public function addNewGmailLabels()
    {
        // pour ajouter au gmail des profs les labels avec l'action add (apr�s ajout de nouveau niveau par exemple)
        $gmail = new \spamtonprof\googleMg\GoogleManager('seb.spamtonprof@gmail.com');

        $profMg = new \spamtonprof\stp_api\StpProfManager();

        $constructor = array(
            "construct" => array(
                'ref_gmail_account'
            )
        );

        $profs = $profMg->getAll(array(
            "inbox_ready" => true
        ), $constructor);

        $labelMg = new \spamtonprof\stp_api\GmailLabelManager();
        $labels = $labelMg->getAll(array(
            'action' => 'add'
        ));

        foreach ($profs as $prof) {

            $gmail = new \spamtonprof\googleMg\GoogleManager($prof->getGmailAcc()->getEmail());

            foreach ($labels as $label) {

                $gmail->createLabel($label->getNom_label(), $label->getColor_label());
                $label->setAction(null);
                $labelMg->updateAction($label);
            }
        }
    }

    // pour ajouter les nouveaux profs aux tags de getresponse et � mettre jour la ref dans stp_matiere
    function resetGrTags()
    {
        $gr = new \GetResponse();

        $profs = $this->getAll();

        foreach ($profs as $prof) {

            $params = new \stdClass();

            $profSigle = $prof->getPrenom() . '_' . $prof->getRef_prof();

            $profSigle = strtolower($profSigle);
            $profSigle = unaccent($profSigle);

            $profSigle = str_replace("-", "_", $profSigle);

            $params->name = $profSigle;

            $tag = $gr->createTag($params);
        }

        $tags = $gr->getTags();

        foreach ($tags as $tag) {

            $tagId = $tag->tagId;

            $tagName = $tag->name;

            $tagNameParts = explode("_", $tagName);

            $refProf = $tagNameParts[count($tagNameParts) - 1];

            $prof = $this->get(array(
                'ref_prof' => $refProf
            ));

            if ($prof) {

                $prof->setGr_id($tagId);
                $this->updateGrId($prof);
            }
        }
    }
}
