<?php
namespace spamtonprof\stp_api;

class StpNiveauManager
{

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function getAll($info)
    {
        $q = null;
        if (in_array('all', $info)) {
            $q = $this->_db->prepare('select * from stp_niveau order by niveau');
            $q->execute();
        }

        $niveaux = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {

            $niveaux[] = new \spamtonprof\stp_api\StpNiveau($data);
        }
        return ($niveaux);
    }

    public function get($info)
    {
        $q = null;
        if (in_array('all', $info)) {
            $q = $this->_db->prepare('select * from stp_niveau order by niveau');
        } else if (array_key_exists('niveau', $info)) {
            $niveau = $info['niveau'];

            $q = $this->_db->prepare('select * from stp_niveau where lower(niveau) like lower(:niveau) limit 1');
            $q->bindValue(':niveau', $niveau);
        } else if (array_key_exists('ref_niveau', $info)) {
            $ref_niveau = $info['ref_niveau'];

            $q = $this->_db->prepare('select * from stp_niveau where ref_niveau = :ref_niveau');
            $q->bindValue(':ref_niveau', $ref_niveau);
        } else if (array_key_exists('sigle', $info)) {
            $sigle = $info['sigle'];

            $q = $this->_db->prepare('select * from stp_niveau where sigle = :sigle');
            $q->bindValue(':sigle', $sigle);
        }

        $q->execute();

        if ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $niveau = new \spamtonprof\stp_api\StpNiveau($data);
            return ($niveau);
        } else {
            return (false);
        }
    }

    // pour ajouter les nouveaux niveaux aux tags de getresponse et à mettre jour la ref dans stp_niveau
    function resetGrTags()
    {
        $gr = new \GetResponse();

        $niveaux = $this->getAll(array(
            'all'
        ));

        foreach ($niveaux as $niveau) {

            $params = new \stdClass();

            $niveauSigle = $niveau->getSigle();

            $niveauSigle = str_replace("-", "_", $niveauSigle);

            $params->name = $niveauSigle;

            $tag = $gr->createTag($params);
        }

        $tags = $gr->getTags();

        foreach ($tags as $tag) {

            $tagId = $tag->tagId;

            $tagName = $tag->name;

            $tagName = str_replace("_", "-", $tagName);

            $niveau = $this->get(array(
                'sigle' => $tagName
            ));

            if ($niveau) {

                $niveau->setGr_id($tagId);
                $this->updateGrId($niveau);
            }
        }
    }

    public static function cast(\spamtonprof\stp_api\StpNiveau $object)
    {
        return ($object);
    }

    public function updateGrId(\spamtonprof\stp_api\StpNiveau $niveau)
    {
        $q = $this->_db->prepare("update stp_niveau set gr_id = :gr_id where ref_niveau = :ref_niveau");
        $q->bindValue(":ref_niveau", $niveau->getRef_niveau());
        $q->bindValue(":gr_id", $niveau->getGr_id());
        $q->execute();
    }
}
