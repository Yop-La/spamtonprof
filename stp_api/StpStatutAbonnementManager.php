<?php
namespace spamtonprof\stp_api;

class StpStatutAbonnementManager
{

    const ACTIF = 1, ESSAI = 2, TERMINE = 3;

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function get($info)
    {
        $q = null;

        if (array_key_exists('ref_statut_abonnement', $info)) {

            $refStatut = $info['ref_statut_abonnement'];

            $q = $this->_db->prepare('select * from stp_statut_abonnement where ref_statut_abonnement = :ref_statut_abonnement');
            $q->bindValue(':ref_statut_abonnement', $refStatut);
            $q->execute();
        } else if (array_key_exists('statut', $info)) {

            $statut = $info['statut'];

            $q = $this->_db->prepare('select * from stp_statut_abonnement where statut_abonnement = :statut_abonnement');
            $q->bindValue(':statut_abonnement', $statut);
            $q->execute();
        }

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\StpStatutAbonnement($data));
        } else {
            return (false);
        }
    }

    public function getAll()
    {
        $q = $this->_db->prepare('select * from stp_statut_abonnement');
        $q->execute();

        $statuts = [];

        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $statuts[] = new \spamtonprof\stp_api\StpStatutAbonnement($data);
        }

        return ($statuts);
    }

    public function updateGrId(\spamtonprof\stp_api\StpStatutAbonnement $statut)
    {
        $q = $this->_db->prepare("update stp_statut_abonnement set gr_id = :gr_id where ref_statut_abonnement = :ref_statut_abonnement");
        $q->bindValue(":ref_statut_abonnement", $statut->getRef_statut_abonnement());
        $q->bindValue(":gr_id", $statut->getGr_id());
        $q->execute();
    }

    // pour ajouter les nouveaux profs aux tags de getresponse et à mettre jour la ref dans stp_matiere
    function resetGrTags()
    {
        $gr = new \GetResponse();

        $status = $this->getAll();

        foreach ($status as $statut) {

            $params = new \stdClass();

            $statutSigle = $statut->getStatut_abonnement();
            $params->name = $statutSigle;

            $tag = $gr->createTag($params);
        }

        $tags = $gr->getTags();

        foreach ($tags as $tag) {

            $tagId = $tag->tagId;

            $tagName = $tag->name;

            $statut = $this->get(array(
                'statut' => $tagName
            ));

            if ($statut) {

                $statut->setGr_id($tagId);
                $this->updateGrId($statut);
            }
        }
    }
}
