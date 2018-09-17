<?php
namespace spamtonprof\stp_api;
class StpSemainePremiumManager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add(StpSemainePremium $StpSemainePremium){
$q = $this->_db->prepare('insert into stp_semaine_premium(ref_statut_premium, ref_premium, ref_abonnement, ref_prof, debut, fin, ref_raison_avortement, date_avortement, prof_paye, probleme_paiement) values( :ref_statut_premium,:ref_premium,:ref_abonnement,:ref_prof,:debut,:fin,:ref_raison_avortement,:date_avortement,:prof_paye,:probleme_paiement)');$q->bindValue(':ref_statut_premium', $StpSemainePremium->getRef_statut_premium());$q->bindValue(':ref_premium', $StpSemainePremium->getRef_premium());$q->bindValue(':ref_abonnement', $StpSemainePremium->getRef_abonnement());$q->bindValue(':ref_prof', $StpSemainePremium->getRef_prof());$q->bindValue(':debut', $StpSemainePremium->getDebut());$q->bindValue(':fin', $StpSemainePremium->getFin());$q->bindValue(':ref_raison_avortement', $StpSemainePremium->getRef_raison_avortement());$q->bindValue(':date_avortement', $StpSemainePremium->getDate_avortement());$q->bindValue(':prof_paye', $StpSemainePremium->getProf_paye());$q->bindValue(':probleme_paiement', $StpSemainePremium->getProbleme_paiement());$q->execute();

$StpSemainePremium->setRef_premium($this->_db->lastInsertId());

return ($StpSemainePremium);}
}
