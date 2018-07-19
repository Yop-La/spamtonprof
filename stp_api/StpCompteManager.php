<?php
namespace spamtonprof\stp_api;
class stpCompteManager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add(stpCompte $stpCompte){
$q = $this->_db->prepare('insert into stp_compte(ref_compte, date_creation, ref_proche, ref_compte_wp) values( :ref_compte,:date_creation,:ref_proche,:ref_compte_wp)');$q->bindValue(':ref_compte', $stpCompte->getRef_compte());$q->bindValue(':date_creation', $stpCompte->getDate_creation());$q->bindValue(':ref_proche', $stpCompte->getRef_proche());$q->bindValue(':ref_compte_wp', $stpCompte->getRef_compte_wp());$q->execute();
//-----------------  à finir ----------------
//-----------------
$stpCompte->setRef_($this->_db->lastInsertId());
//-----------------  à finir ----------------
//-----------------
return ($stpCompte);}
}
