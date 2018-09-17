<?php
namespace spamtonprof\stp_api;
class Proche extends Personne implements \JsonSerializable
{
  private $ref_parent;
  

  public function setRef_parent($ref_parent)

  {

      $this->ref_parent = $ref_parent;

  }


  public function ref_parent()

  {

    return $this->ref_parent;

  }

  public function jsonSerialize()
  {
      $vars = get_object_vars($this);

      return $vars;
  }
 
}