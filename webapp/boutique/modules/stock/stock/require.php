<?php 
namespace Home;

$produits = PRODUIT::findBy(["isActive ="=>TABLE::OUI]);

$parfums = PARFUM::findBy(["isActive ="=>TABLE::OUI]);
$typeproduits = TYPEPRODUIT::findBy(["isActive ="=>TABLE::OUI]);
$quantites = QUANTITE::findBy(["isActive ="=>TABLE::OUI]);
$produits = PRODUIT::findBy(["isActive ="=>TABLE::OUI]);

$title = "GPV | Votre stock en boutique ";

?>