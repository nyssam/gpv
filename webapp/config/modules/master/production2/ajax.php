<?php 
namespace Home;
use Native\ROOTER;
require '../../../../../core/root/includes.php';
use Native\RESPONSE;

$data = new RESPONSE;
extract($_POST);


if ($action == "exigence") {
	$datas = EXIGENCEPRODUCTION::findBy(["id ="=>$id]);
	if (count($datas) == 1) {
		$exi = $datas[0];
		$exi->quantite = $quantite;
		$data = $exi->save();
		if ($data->status) {
			foreach ($_POST as $key => $value) {
				$datas = LIGNEEXIGENCEPRODUCTION::findBy(["id ="=>$key]);
				if (count($datas) == 1) {
					$ligne = $datas[0];
					$ligne->quantite = $value;
					$ligne->save();
				}
			}
		}
	}
	echo json_encode($data);
}




if ($action == "changementPrice") {
	$datas = PRICE::findBy(["id ="=>$id]);
	if (count($datas) == 1) {
		$prix = $datas[0];
		$prix->$name = intval($val);
		$data = $prix->save();
	}
	echo json_encode($data);
}


if ($action == "changement") {
	$datas = PRODUIT::findBy(["id ="=>$id]);
	if (count($datas) == 1) {
		$prix = $datas[0];
		$prix->$name = intval($val);
		$data = $prix->save();
	}
	echo json_encode($data);
}


//disponiblité des elements
if ($action === "changeProductionAuto") {
	$params = PARAMS::findLastId();
	if ($params->productionAuto == TABLE::OUI) {
		$params->productionAuto = TABLE::NON;
	}else{
		$params->productionAuto = TABLE::OUI;
	}
	$data = $params->save();
	echo json_encode($data);
}