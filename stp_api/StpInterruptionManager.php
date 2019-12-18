<?php
namespace spamtonprof\stp_api;

class StpInterruptionManager
{

    const scheduled = 'scheduled', running = 'running', done = 'done', stopping = 'stopping';

    private $_db;

    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function add(StpInterruption $stpInterruption)
    {
        $q = $this->_db->prepare('insert into stp_interruption(debut, fin, statut,ref_abonnement) values(:debut,:fin,:statut,:ref_abonnement)');
        $q->bindValue(':debut', $stpInterruption->getDebut());
        $q->bindValue(':fin', $stpInterruption->getFin());
        $q->bindValue(':statut', $stpInterruption->getStatut());
        $q->bindValue(':ref_abonnement', $stpInterruption->getRef_abonnement());

        $q->execute();


        $stpInterruption->setRef_interruption($this->_db->lastInsertId());
        return ($stpInterruption);
    }
    
    
    public function isValidInterruption(\DateTime $date_debut, \DateTime $date_fin, $refAbonnement){
        
        
        $retour = new \stdClass();
        $retour->valide=false;
        
        if($date_debut > $date_fin){
            $retour->message = 'La date de début doit être avant la date de fin :) :)';
            return($retour);

        }
        
        $ecart = $date_fin->diff($date_debut);
        $ecart = $ecart->format('%d%');
        
        if($ecart < 5){
            $retour->message = "L'interruption doit être d'au moins 5 jours !";
            return($retour);
        }
        
        if($ecart < 5){
            $retour->message = "L'interruption doit être d'au moins 5 jours !";
            return($retour);
        }
        
        if($ecart > 15 && $ecart <= 31){
            $retour->message = "L'interruption doit être de 15 ou de 31 jours au plus ";
            return($retour);
        }
        
        $q = $this->_db->prepare("select * from stp_interruption where 
            :debut >= debut and :debut <= fin
            and :fin >= debut and :fin <= fin
            and ref_abonnement = :ref_abonnement");
        
        $q->bindValue(':debut', $date_debut->format(PG_DATE_FORMAT));
        $q->bindValue(':fin', $date_debut->format(PG_DATE_FORMAT));
        $q->bindValue(':ref_abonnement', $refAbonnement);
        $q->execute();
        
        $rowCount = $q->rowCount();
        
        
        if($rowCount != 0){
            $retour->message = "Les dates demandées chevauchent les dates d'une autre interruption";
            return($retour);
        }
        
        
        $retour->valide=true;
        return($retour);
        
        
    }
    

    public function getAll($info=false,$constructor=false)
    {
        
        $q = $this->_db->prepare("select * from stp_interruption ");
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'to_start') {

                    $q = $this->_db->prepare("select * from stp_interruption where statut = 'scheduled' and now() > debut and now() < fin");
                }
                
                if ($key == 'of_an_account') {
                    
                    $q = $this->_db->prepare("select * from stp_interruption where ref_abonnement in ( select ref_abonnement from stp_abonnement where ref_compte = :ref_compte )");
                    $q->bindValue('ref_compte', $params['ref_compte']);
                }
                
                if ($key == 'to_stop') {
                    
                    $q = $this->_db->prepare("select * from stp_interruption where statut = 'stopping' ");
                }
                
                if ($key == 'all') {
                    
                 
                }
                
            } else {
                if (array_key_exists('debut', $info)) {

                    $deb = $info['debut'];
                    $q = $this->_db->prepare('select * from stp_interruption where debut = :debut');
                    $q->bindValue(':debut', $deb);
                    $q->execute();
                } else if (array_key_exists('fin', $info)) {

                    $fin = $info['fin'];
                    $q = $this->_db->prepare('select * from stp_interruption where fin = :fin');
                    $q->bindValue(':fin', $fin);
                    $q->execute();
                } else if (array_key_exists('prolongation', $info)) {

                    $prolongation = $info['prolongation'];
                    $q = $this->_db->prepare('select * from stp_interruption where prolongation = :prolongation');
                    $q->bindValue(':prolongation', $prolongation);
                    $q->execute();
                }
            }
        }

        $q->execute();

        $interrups = [];
        while ($data = $q->fetch(\PDO::FETCH_ASSOC)) {
            $interrup = new \spamtonprof\stp_api\StpInterruption($data);
            
            
            if ($constructor) {
                $constructor["objet"] = $interrup;
                $this->construct($constructor);
            }
            
            $interrups[] = $interrup;
        }
        return ($interrups);
    }
    
    
    public static function cast(StpInterruption $interruption){
        return($interruption);
    }
    
    
    public function construct($constructor)
    {
        
        $interruption = StpInterruptionManager::cast($constructor["objet"]);
        
        $constructOrders = $constructor["construct"];
        
        foreach ($constructOrders as $constructOrder) {
            
            switch ($constructOrder) {
                case "ref_abonnement":
                    $aboMg = new \spamtonprof\stp_api\StpAbonnementManager();
                    $abo = $aboMg->get(array(
                        'ref_abonnement' => $interruption->getRef_abonnement()
                    ));
                    
                    if (array_key_exists("ref_abonnement", $constructor)) {
                        
                        $constructorAbo = $constructor["ref_abonnement"];
                        $constructorAbo["objet"] = $abo;
                        
                        $aboMg->construct($constructorAbo);
                    }
                    $interruption->setAbo($abo);
                    break;
            }
        }
    }
    
    
    
    
    
    
    public function deleteAll()
    {
        $q = $this->_db->prepare("delete from stp_interruption");
        $q->execute();
    }

    public function updateFin(\spamtonprof\stp_api\StpInterruption $interrup)
    {
        $q = $this->_db->prepare("update stp_interruption set fin = :fin where ref_abonnement = :ref_abonnement");
        $q->bindValue(":ref_abonnement", $interrup->getRef_abonnement());
        $q->bindValue(":fin", $interrup->getFin());
        $q->execute();
    }

    public function update_statut(\spamtonprof\stp_api\StpInterruption $interrup)
    {
        $q = $this->_db->prepare("update stp_interruption set statut = :statut where ref_interruption = :ref_interruption");
        $q->bindValue(":ref_interruption", $interrup->getRef_interruption());
        $q->bindValue(":statut", $interrup->getStatut());
        $q->execute();
    }

    public function get($info)
    {
        $q = null;
        if (is_array($info)) {

            if (array_key_exists('key', $info)) {
                $key = $info['key'];
                $params = false;
                if (array_key_exists('params', $info)) {
                    $params = $info['params'];
                }

                if ($key == 'to_stop') {
                    
                    $refAbo = $params['ref_abo'];

                    $q = $this->_db->prepare("select * from stp_interruption where ref_abonnement = :ref_abonnement and statut = 'running'");
                    $q->bindValue(":ref_abonnement", $refAbo);
                    
                    
                }
            } else {

                if (array_key_exists('currentOrNextInterruption', $info)) {
                    $refAbo = $info['currentOrNextInterruption'];
                    $q = $this->_db->prepare('select * from stp_interruption where fin >= current_date and ref_abonnement = :ref_abonnement order by fin ');
                    $q->bindValue(":ref_abonnement", $refAbo);
                }
            }
        }

        $q->execute();

        
        $data = $q->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $interrup = new \spamtonprof\stp_api\StpInterruption($data);
            return ($interrup);
        } else {
            return (false);
        }
    }
}
