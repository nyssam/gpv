<?php 
namespace Home;

$emballages = EMBALLAGE::findBy(["isActive ="=>TABLE::OUI], [], ["name"=>"ASC"]);
$quantites = QUANTITE::findBy(["isActive ="=>TABLE::OUI], [], ["name"=>"ASC"]);
$parfums = PARFUM::findBy(["isActive ="=>TABLE::OUI], [], ["name"=>"ASC"]);
$types_parfums = TYPEPRODUIT_PARFUM::findBy(["isActive ="=>TABLE::OUI]);
$types = TYPEPRODUIT::findBy(["isActive ="=>TABLE::OUI], [], ["name"=>"ASC"]);
$produits = PRODUIT::findBy(["isActive ="=>TABLE::OUI]);
$ressources = RESSOURCE::getAll([], [], ["name"=>"ASC"]);

$title = "GPV | Paramètres de configuration des prix de vente";



?>