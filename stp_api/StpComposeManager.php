<?php
namespace spamtonprof\stp_api;
class stpComposeManager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add(stpCompose $stpCompose){
$q = $this->_db->prepare('insert into stp_compose(ref_stp_compose, ref_eleve, ref_compte) values( :ref_stp_compose,:ref_eleve,:ref_compte)');$q->bindValue(':ref_stp_compose', $stpCompose->getRef_stp_compose());$q->bindValue(':ref_eleve', $stpCompose->getRef_eleve());$q->bindValue(':ref_compte', $stpCompose->getRef_compte());$q->execute();
//-----------------  à finir ----------------
//-----------------
$stpCompose->setRef_($this->_db->lastInsertId());
//-----------------  à finir ----------------
//-----------------
return ($stpCompose);}
}
