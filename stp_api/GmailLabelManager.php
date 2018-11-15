<?php
namespace spamtonprof\stp_api;

use PDO;

class GmailLabelManager

{

    private $_db;

    // Instance de PDO
    public function __construct()

    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(GmailLabel $gmailLabel)
    {
        $q = $this->_db->prepare('INSERT INTO gmail_label(nom_label, color_label, action) VALUES(:nom_label, :color_label, :action)');
        $q->bindValue(':nom_label', $gmailLabel->getNom_label());
        $q->bindValue(':color_label', $gmailLabel->getColor_label());
        $q->bindValue(':action', $gmailLabel->getAction());
        $q->execute();

        $gmailLabel->setRef_label($this->_db->lastInsertId());
        return ($gmailLabel);
    }

    public function getAll($info = false)
    {
        $labels = [];

        $q = null;
        if (! $info) {
            $q = $this->_db->prepare("SELECT ref_label, nom_label, color_label FROM gmail_label");
        } else if (is_array($info)) {

            if (array_key_exists('color_label', $info)) {
                $colorLabel = $info['color_label'];
                $q = $this->_db->prepare("SELECT * FROM gmail_label where color_label = :color_label");
                $q->bindValue(":color_label", $colorLabel);
            } else if (array_key_exists('action', $info)) {
                $action = $info['action'];
                $q = $this->_db->prepare("SELECT * FROM gmail_label where action = :action");
                $q->bindValue(":action", $action);
            }
        }

        $q->execute();

        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $label = new GmailLabel($donnees);
            $labels[] = $label;
        }

        return $labels;
    }

    public function get($info)
    {
        if (is_array($info)) {

            if (array_key_exists('nom_label', $info)) {
                $nomLabel = $info['nom_label'];
                $q = $this->_db->prepare("SELECT * FROM gmail_label where nom_label = :nom_label");
                $q->bindValue(":nom_label", $nomLabel);
            }
        }

        $q->execute();

        if ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $label = new GmailLabel($donnees);
            return ($label);
        }

        return false;
    }

    public function getAllLabelName()
    {
        $labelNames = [];

        $q = $this->_db->prepare("SELECT nom_label FROM gmail_label");
        $q->execute();

        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {

            $labelNames[] = $donnees['nom_label'];
        }

        return $labelNames;
    }

    public function updateAction(\spamtonprof\stp_api\GmailLabel $label)
    {
        $q = $this->_db->prepare("update gmail_label set action = :action where ref_label = :ref_label");
        $q->bindValue(":action", $label->getAction());
        $q->bindValue(":ref_label", $label->getRef_label());
        $q->execute();
    }

    // pour ajouter à la table gmail_label les nouveaux niveaux ajoutés à la table stp_niveau
    function addNewNiveaux()
    {
        $niveauMg = new \spamtonprof\stp_api\StpNiveauManager();

        $niveaux = $niveauMg->getAll(array(
            'all'
        ));

        // pour ajouter les nouveaux niveaux à la table
        foreach ($niveaux as $niveau) {

            $label = $this->get(array(
                'nom_label' => $niveau->getSigle()
            ));

            // si aucun label correspondant on l'ajoute avec action add
            if (! $label) {
                echo ($niveau->getSigle() . '<br>');
                $label = new \spamtonprof\stp_api\GmailLabel(array(
                    'nom_label' => $niveau->getSigle(),
                    'color_label' => '#cccccc',
                    'action' => 'add'
                ));
                $this->add($label);
            }
        }
    }
}