<?php
namespace spamtonprof\stp_api;

class StpMatiereManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpMatiere $StpMatiere)
    {
        $q = $this->_db->prepare('insert into stp_matiere(ref_matiere, matiere) values( :ref_matiere,:matiere)');
        $q->bindValue(':ref_matiere', $StpMatiere->getRef_matiere());
        $q->bindValue(':matiere', $StpMatiere->getMatiere());
        $q->execute();

        $StpMatiere->setRef_matiere($this->_db->lastInsertId());

        return ($StpMatiere);
    }

    public static function cast(\spamtonprof\stp_api\StpMatiere $object)
    {
        return ($object);
    }

    public function get($info)
    {
        $q = null;
        if (array_key_exists('matiere', $info)) {

            $matiere = $info['matiere'];

            $q = $this->_db->prepare('select * from stp_matiere where matiere like :matiere');
            $q->bindValue(':matiere', $matiere);
        }

        if (array_key_exists('ref_matiere', $info)) {

            $refMatiere = $info['ref_matiere'];

            $q = $this->_db->prepare('select * from stp_matiere where ref_matiere = :ref_matiere');
            $q->bindValue(':ref_matiere', $refMatiere);
        }

        if (array_key_exists('matiere_complet', $info)) {

            $matiereComplet = $info['matiere_complet'];

            $q = $this->_db->prepare('select * from stp_matiere where lower(matiere_complet) like lower(:matiere_complet)');
            $q->bindValue(':matiere_complet', $matiereComplet);
        }

        $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            return (new \spamtonprof\stp_api\StpMatiere($data));
        } else {
            return (false);
        }
    }

    public function getAll($info)
    {
        $q = null;
        if (in_array('all', $info)) {
            $q = $this->_db->prepare('select * from stp_matiere');
            $q->execute();
        }

        $matieres = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $matieres[] = new \spamtonprof\stp_api\StpMatiere($data);
        }
        return ($matieres);
    }

    public function updateGrId(\spamtonprof\stp_api\StpMatiere $matiere)
    {
        $q = $this->_db->prepare("update stp_matiere set gr_id = :gr_id where ref_matiere = :ref_matiere");
        $q->bindValue(":ref_matiere", $matiere->getRef_matiere());
        $q->bindValue(":gr_id", $matiere->getGr_id());
        $q->execute();
    }

    // pour ajouter les nouveaux matières aux tags de getresponse et à mettre jour la ref dans stp_matiere
    function resetGrTags()
    {
        $gr = new \GetResponse();

        $matieres = $this->getAll(array(
            'all'
        ));

        foreach ($matieres as $matiere) {

            $params = new \stdClass();

            $matiereSigle = $matiere->getMatiere();

            $params->name = $matiereSigle;

            $tag = $gr->createTag($params);
        }

        $tags = $gr->getTags();

        foreach ($tags as $tag) {

            $tagId = $tag->tagId;

            $tagName = $tag->name;

            $matiere = $this->get(array(
                'matiere' => $tagName
            ));

            if ($matiere) {

                $matiere->setGr_id($tagId);
                $this->updateGrId($matiere);
            }
        }
    }
}
