<?php
namespace spamtonprof\stp_api;
class Manager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add( $){
$q = $this->_db->prepare('insert into stp_classe(classe, ref_classe) values( :classe,:ref_classe)');$q->bindValue(':classe', $->getClasse());$q->bindValue(':ref_classe', $->getRef_classe());$q->execute();
//-----------------  à finir ----------------
//-----------------
$->setRef_($this->_db->lastInsertId());
//-----------------  à finir ----------------
//-----------------
return ($);}
}
