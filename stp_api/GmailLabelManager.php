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
        $q = $this->_db->prepare('INSERT INTO gmail_label(nom_label, color_label) VALUES(:nom_label, :color_label)');
        $q->bindValue(':nom_label', $gmailLabel->getNom_label());
        $q->bindValue(':color_label', $gmailLabel->getColor_label());
        $q->execute();
        
        $gmailLabel->setRef_label($this->_db->lastInsertId());
        return ($gmailLabel);
    }
    
    public function getAll()
    {
        $labels = [];
        
        $q = $this->_db->prepare("SELECT ref_label, nom_label, color_label FROM gmail_label");
        $q->execute();
        
        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $label = new GmailLabel($donnees);
            $labels[] = $label;
        }
        
        return $labels;
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
    
}