<?php
namespace spamtonprof\stp_api;

use PDO;

class EleveManager
{

    private $_db;

    // Instance de PDO
    public function __construct()
    {
        $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
    }

    public function existsOld($info)
    {
        if (is_int($info)) // On veut voir si tel personnage ayant pour id $info existe.
        {
            return boolval($this->_db->query('SELECT COUNT(*) FROM eleve WHERE ref_eleve = ' . $info)->fetchColumn());
        }
        
        // Sinon, c'est qu'on veut vérifier que le nom existe ou pas.
        
        $q = $this->_db->prepare("SELECT COUNT(*) FROM eleve WHERE adresse_mail like ?");
        $q->execute(array(
            '%' . $info . '%'
        ));
        return boolval($q->fetchColumn());
    }

    public function get($info)
    {
        if (is_int($info)) {
            $q = $this->_db->query('SELECT * FROM eleve WHERE ref_eleve = ' . $info);
            
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                return new Eleve($q->fetch(PDO::FETCH_ASSOC));
            }
        } else {
            $q = $this->_db->prepare('SELECT * FROM eleve WHERE adresse_mail like :mail');
            $q->execute([
                ':mail' => '%' . $info . '%'
            ]);
            if ($q->rowCount() <= 0) {
                return (false);
            } else {
                $res = $q->fetch(PDO::FETCH_ASSOC);
                return new Eleve($res);
            }
        }
    }

    public function add(Eleve $eleve)
    {
        $q = $this->_db->prepare('insert into eleve(prenom, nom, telephone, adresse_mail, update_date, date_created, classe)
                                      values(:prenom, :nom, :telephone, :adresse_mail, :update_date, :date_created, :classe)');
        $q->bindValue(':prenom', $eleve->prenom());
        $q->bindValue(':nom', $eleve->nom());
        $q->bindValue(':telephone', $eleve->getTelephone());
        $q->bindValue(':classe', $eleve->classe());
        
        $now = new \DateTime(null, new \DateTimeZone("Europe/Paris"));
        
        $q->bindValue(':adresse_mail', $eleve->adresse_mail());
        $q->bindValue(':update_date', $now->format(PG_DATETIME_FORMAT));
        $q->bindValue(':date_created', $now->format(PG_DATETIME_FORMAT));
        
        $q->execute();
        
        $eleve->setRef_eleve($this->_db->lastInsertId());
        return ($eleve);
    }

    public function delete($info)
    {
        if (is_int($info)) {
            
            $q = $this->_db->prepare('delete from eleve where ref_eleve = :ref_eleve');
            $q->bindValue(':ref_eleve', $info);
            $q->execute();
            
        }else if (is_string($info)){
            
            $q = $this->_db->prepare('delete from eleve where adresse_mail = :adresse_mail');
            $q->bindValue(':adresse_mail', $info);
            $q->execute();
            
        }
        
        return;
    }

    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
}