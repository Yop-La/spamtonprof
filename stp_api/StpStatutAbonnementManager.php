<?php
namespace spamtonprof\stp_api;
class stpStatutAbonnementManager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add(stpStatutAbonnement $stpStatutAbonnement){
$q = $this->_db->prepare('insert into stp_statut_abonnement(ref_statut_abonnement, statut_abonnement) values( :ref_statut_abonnement,:statut_abonnement)');$q->bindValue(':ref_statut_abonnement', $stpStatutAbonnement->getRef_statut_abonnement());$q->bindValue(':statut_abonnement', $stpStatutAbonnement->getStatut_abonnement());$q->execute();

$stpStatutAbonnement->setRef_statut_abonnement($this->_db->lastInsertId());

return ($stpStatutAbonnement);}
}
