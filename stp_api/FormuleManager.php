<?php
namespace spamtonprof\stp_api;
use PDO;
class FormuleManager
{
  private $_db; // Instance de PDO
  
  public function __construct()
  {
      $this->_db = \spamtonprof\stp_api\PdoManager::getBdd();
  }
  
  public function get($info)
  {
    if (is_int($info))
    {
      $q = $this->_db->prepare('SELECT formule, ref_formule, classes, maths, physique, francais, id_stripe, id_stripe_test FROM formule WHERE ref_formule = :ref_formule');
      $q->execute([':ref_formule' => $info]);

      if($q->rowCount() <= 0){
        return(false);
      }else{
        $data = $q->fetch(PDO::FETCH_ASSOC);
        return new Formule($data);
      } 
    }else if(is_array($info)){
      $q = $this->_db->prepare('SELECT formule, ref_formule, classes, maths, physique, francais, id_stripe, id_stripe_test FROM formule WHERE :classe  = any(classes) and francais = :francais and maths = :maths and physique = :physique');
      $q->bindValue(':classe', $info["classe"],PDO::PARAM_STR);
      $q->bindValue(':maths', $info["maths"],PDO::PARAM_BOOL);
      $q->bindValue(':physique', $info["physique"],PDO::PARAM_BOOL);
      $q->bindValue(':francais', $info["francais"],PDO::PARAM_BOOL);
      $q->execute();
      if($q->rowCount() <= 0){
        return(false);
      }else{
        $data = $q->fetch(PDO::FETCH_ASSOC);
        return new Formule($data);
      } 
    }
  }

  
  
  public function getAll(): array
  {
    $formules = [];
    $q = $this->_db->prepare('SELECT formule, ref_formule, classes, maths, physique, francais, id_stripe, id_stripe_test FROM formule');
    $q->execute();
    
    while ($donnees = $q->fetch(PDO::FETCH_ASSOC))
    {
      $formules[] = new Formule($donnees);
    }
    
    return $formules;
  }
  
  public function setDb(PDO $db)
  {
    $this->_db = $db;
  }

  public function update(Formule $formule)

  {

    $q = $this->_db->prepare('
      UPDATE formule SET formule = :formule, 
        classes = :classes, 
        maths = :maths, 
        physique = :physique, 
        francais = :francais, 
        id_stripe = :id_stripe,
        id_stripe_test = :id_stripe_test
      WHERE ref_formule = :ref_formule');

    

    $q->bindValue(':formule', $formule->formule());

    $q->bindValue(':classes', $formule->classes());

    $q->bindValue(':maths', $formule->maths(), PDO::PARAM_BOOL);

    $q->bindValue(':physique', $formule->physique(), PDO::PARAM_BOOL);

    $q->bindValue(':francais', $formule->francais(), PDO::PARAM_BOOL);

    $q->bindValue(':id_stripe', $formule->id_stripe());
    
    $q->bindValue(':id_stripe_test', $formule->id_stripe_test());

    $q->bindValue(':ref_formule', $formule->ref_formule(), PDO::PARAM_INT);

    $q->execute();

  }
}