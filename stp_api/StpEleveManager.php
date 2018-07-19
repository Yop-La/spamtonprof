<?php
namespace spamtonprof\stp_api;
class stpEleveManager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add(stpEleve $stpEleve){
$q = $this->_db->prepare('insert into stp_eleve(email, prenom, ref_classe, nom, telephone, ref_eleve) values( :email,:prenom,:ref_classe,:nom,:telephone,:ref_eleve)');$q->bindValue(':email', $stpEleve->getEmail());$q->bindValue(':prenom', $stpEleve->getPrenom());$q->bindValue(':ref_classe', $stpEleve->getRef_classe());$q->bindValue(':nom', $stpEleve->getNom());$q->bindValue(':telephone', $stpEleve->getTelephone());$q->bindValue(':ref_eleve', $stpEleve->getRef_eleve());$q->execute();
//-----------------  à finir ----------------
//-----------------
$stpEleve->setRef_($this->_db->lastInsertId());
//-----------------  à finir ----------------
//-----------------
return ($stpEleve);}
}
