<?php 
namespace Home;

$title = "GPV | Toutes les pertes entrepots";

unset_session("produits");

$produits = PRODUIT::findBy(["isActive ="=>TABLE::OUI]);


$datas = $entrepot->fourni("transfertstockentrepot", ["DATE(created) >="=>$date1, "DATE(created) <="=>$date2], [], ["created"=>"DESC"]);

?>