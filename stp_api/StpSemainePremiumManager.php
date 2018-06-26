<?php
namespace spamtonprof\stp_api;
class stpSemainePremiumManager 
 { private $_db; public function __construct() { $this->_db = \spamtonprof\stp_api\PdoManager::getBdd(); } public function add(stpSemainePremium $stpSemainePremium){
$q = $this->_db->prepare('insert into stp_semaine_premium(ref_statut_premium, ref_premium, ref_abonnement, ref_prof, debut, fin, ref_raison_avortement, date_avortement, prof_paye, probleme_paiement) values( :ref_statut_premium,:ref_premium,:ref_abonnement,:ref_prof,:debut,:fin,:ref_raison_avortement,:date_avortement,:prof_paye,:probleme_paiement)');$q->bindValue(':ref_statut_premium', $stpSemainePremium->getRef_statut_premium());$q->bindValue(':ref_premium', $stpSemainePremium->getRef_premium());$q->bindValue(':ref_abonnement', $stpSemainePremium->getRef_abonnement());$q->bindValue(':ref_prof', $stpSemainePremium->getRef_prof());$q->bindValue(':debut', $stpSemainePremium->getDebut());$q->bindValue(':fin', $stpSemainePremium->getFin());$q->bindValue(':ref_raison_avortement', $stpSemainePremium->getRef_raison_avortement());$q->bindValue(':date_avortement', $stpSemainePremium->getDate_avortement());$q->bindValue(':prof_paye', $stpSemainePremium->getProf_paye());$q->bindValue(':probleme_paiement', $stpSemainePremium->getProbleme_paiement());$q->execute();

$stpSemainePremium->setRef_premium($this->_db->lastInsertId());

return ($stpSemainePremium);}
}
