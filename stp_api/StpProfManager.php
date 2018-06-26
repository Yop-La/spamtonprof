<?php
namespace spamtonprof\stp_api;
class stpProfManager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add(stpProf $stpProf){
$q = $this->_db->prepare('insert into stp_prof(email, prenom, nom, telephone, ref_prof) values( :email,:prenom,:nom,:telephone,:ref_prof)');$q->bindValue(':email', $stpProf->getEmail());$q->bindValue(':prenom', $stpProf->getPrenom());$q->bindValue(':nom', $stpProf->getNom());$q->bindValue(':telephone', $stpProf->getTelephone());$q->bindValue(':ref_prof', $stpProf->getRef_prof());$q->execute();
//-----------------  à finir ----------------
//-----------------
$stpProf->setRef_($this->_db->lastInsertId());
//-----------------  à finir ----------------
//-----------------
return ($stpProf);}
}
