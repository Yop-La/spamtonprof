<?php
namespace spamtonprof\stp_api;
class stpClasse implements \JsonSerializable
{
protected
$classe, 
$ref_classe, 
$ref_profil, 
$nom_complet;
 public function __construct(array $donnees = array()) { $this->hydrate($donnees); } public function hydrate(array $donnees) { foreach ($donnees as $key => $value) { $method = "set" . ucfirst($key); if (method_exists($this, $method)) { $this->$method($value); } } }public function getClasse()
{
return $this->classe;
}
public function setClasse($classe)
{
$this->classe = $classe;
}
public function getRef_classe()
{
return $this->ref_classe;
}
public function setRef_classe($ref_classe)
{
$this->ref_classe = $ref_classe;
}
public function getRef_profil()
{
return $this->ref_profil;
}
public function setRef_profil($ref_profil)
{
$this->ref_profil = $ref_profil;
}
public function getNom_complet()
{
return $this->nom_complet;
}
public function setNom_complet($nom_complet)
{
$this->nom_complet = $nom_complet;
}
 public function jsonSerialize() { $vars = get_object_vars($this); return $vars; }}