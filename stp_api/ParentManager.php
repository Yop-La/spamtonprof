<?php
namespace spamtonprof\stp_api;
use PDO;
class ParentManager
{
  private $_db; // Instance de PDO
  
  public function __construct()
  {
      $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
  }

  // public function delete(Personnage $perso)
  // {
  //   $this->_db->exec('DELETE FROM personnages WHERE id = '.$perso->id());
  // }
  
  public function existsOld($info)
  {
    if (is_int($info)) // On veut voir si tel personnage ayant pour id $info existe.
    {
      return boolval($this->_db->query('SELECT COUNT(*) FROM parent WHERE ref_parent = '.$info)->fetchColumn());
    }
    
    // Sinon, c'est qu'on veut vérifier que le nom existe ou pas.
    
    $q = $this->_db->prepare("SELECT COUNT(*) FROM parent WHERE adresse_mail like ?");
    $q->execute(array('%'.$info.'%'));
    return boolval($q->fetchColumn());
  }


  
  public function get($info)
  {
    if (is_int($info))
    {
      $q = $this->_db->query('SELECT * FROM parent WHERE ref_parent = '.$info);

      if($q->rowCount() <= 0){
        return(false);
      }else{
        return new Proche($q->fetch(PDO::FETCH_ASSOC));
      } 
    }
    else
    {
      $q = $this->_db->prepare('SELECT * WHERE adresse_mail like :mail');
      $q->execute([':mail' => '%' . $info . '%']);
      if($q->rowCount() <= 0){
        return(false);
      }else{
        $res = $q->fetch(PDO::FETCH_ASSOC);
        return new Proche($res);  
      } 
    }
  }
  
  public function add(Proche $proche){
      
      $q = $this->_db->prepare('insert into parent(prenom, nom, telephone, adresse_mail, update_date, date_created) 
                                      values(:prenom, :nom, :telephone, :adresse_mail, :update_date, :date_created)');
      $q->bindValue(':prenom', $proche->prenom());
      $q->bindValue(':nom', $proche->nom());
      $q->bindValue(':telephone', $proche->getTelephone());
      
      
      $now = new \DateTime(null,new \DateTimeZone("Europe/Paris"));
      
      $q->bindValue(':adresse_mail', $proche->adresse_mail());
      $q->bindValue(':update_date', $now->format(PG_DATETIME_FORMAT));
      $q->bindValue(':date_created', $now->format(PG_DATETIME_FORMAT));
      
      $q->execute();
      
      $proche->setRef_parent($this->_db->lastInsertId());
      return ($proche);
      
  }
  
  public function delete($info){
      if(is_int($info)){
          
      $q = $this->_db->prepare('delete from parent where ref_parent = :ref_parent');
      $q->bindValue(':ref_parent', $info);
      $q->execute();
      
      }else if(is_string($info)){
          
          $q = $this->_db->prepare('delete from parent where adresse_mail = :adresse_mail');
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