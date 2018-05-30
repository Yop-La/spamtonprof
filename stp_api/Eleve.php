<?php
namespace spamtonprof\stp_api;

class Eleve extends Personne  implements \JsonSerializable
{
  private $ref_eleve,

          $classe;

  public function setRef_eleve($ref_eleve)

  {

      $this->ref_eleve = $ref_eleve;

  }


  public function ref_eleve()

  {

    return $this->ref_eleve;

  }


  public function setClasse($classe)

  { 
    
      $this->classe = $classe;

  }


  public function classe()

  {

    return $this->classe;

  }


  public function jsonSerialize()
  {
      $vars = get_object_vars($this);

      return $vars;
  }
}