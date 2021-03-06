<?php 
namespace Home;
require '../../../../../core/root/includes.php';

use Native\RESPONSE;

$data = new RESPONSE;
extract($_POST);

unset_session("emballages-disponibles");
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if ($action == "annulerConditionnement") {
	$datas = EMPLOYE::findBy(["id = "=>getSession("employe_connecte_id")]);
	if (count($datas) > 0) {
		$employe = $datas[0];
		$employe->actualise();
		if ($employe->checkPassword($password)) {
			$datas = CONDITIONNEMENT::findBy(["id ="=>$id]);
			if (count($datas) == 1) {
				$conditionnement = $datas[0];
				$data = $conditionnement->annuler();
			}else{
				$data->status = false;
				$data->message = "Une erreur s'est produite lors de l'opération! Veuillez recommencer";
			}
		}else{
			$data->status = false;
			$data->message = "Votre mot de passe ne correspond pas !";
		}
	}else{
		$data->status = false;
		$data->message = "Vous ne pouvez pas effectué cette opération !";
	}
	echo json_encode($data);
}



if ($action == "validerConditionnement") {
	$quantite = 0;
	$test = true;
	foreach ($_POST as $key => $value) {
		if (strpos($key, "-") !== false && $value > 0) {
			$tab = explode("-", $key);
			$datas = PRODUIT::findBy(["id ="=>$tab[0]]);
			if (count($datas) == 1) {
				$produit = $datas[0];
				$datas = EMBALLAGE::findBy(["id ="=>$tab[1]]);
				if (count($datas) == 1) {
					$produit->actualise();
					$emballage = $datas[0];
					if ($emballage->isDisponible($value)) {
						$quantite += $emballage->nombre() * $produit->quantite->name * $value;
					}else{
						$test = false;
						break;
					}
				}
			}
		}
	}
	if ($test) {
		$test = true;
		foreach (getSession("emballages-disponibles") as $key => $value) {
			$datas = EMBALLAGE::findBy(["id ="=>$key]);
			if (count($datas) == 1) {
				$emballage = $datas[0];
				if ($emballage->comptable == TABLE::OUI) {
					if ($emballage->stock(PARAMS::DATE_DEFAULT, dateAjoute(1), getSession("entrepot_connecte_id")) < $value) {
						$test = false;
						break;
					}
				}
			}else{
				$test = false;
				break;
			}
		}
		if ($test) {
			if ($quantite <= $produit->typeproduit_parfum->enStock(PARAMS::DATE_DEFAULT, dateAjoute(1), getSession("entrepot_connecte_id"))) {
				$conditionnement = new CONDITIONNEMENT();
				$conditionnement->hydrater($_POST);
				$conditionnement->typeproduit_parfum_id = $produit->typeproduit_parfum_id;
				$conditionnement->quantite = $quantite;
				$data = $conditionnement->enregistre();
				if ($data->status) {
					if (count($datas) > 0) {
						foreach ($_POST as $key => $value) {
							if (strpos($key, "-") !== false && $value > 0) {
								$tab = explode("-", $key);
								$datas = PRODUIT::findBy(["id ="=>$tab[0]]);
								if (count($datas) == 1) {
									$produit = $datas[0];
									$datas = EMBALLAGE::findBy(["id ="=>$tab[1]]);
									$emballage = $datas[0];
									if (count($datas) == 1) {
										$ligne = new LIGNECONDITIONNEMENT;
										$ligne->conditionnement_id = $conditionnement->id;
										$ligne->produit_id = $produit->id;
										$ligne->emballage_id = $emballage->id;
										$ligne->quantite = $value;
										$ligne->enregistre();

										$emballage->packaging($value, $conditionnement->id);							


										$ligne = new LIGNECONSOMMATIONETIQUETTE();
										$etiquette = ($produit->fourni("etiquette"))[0];

										$ligne->conditionnement_id = $conditionnement->id;
										$ligne->etiquette_id = $etiquette->id;
										$ligne->quantite = $value * $emballage->nombre();
										$ligne->price = $ligne->quantite * $emballage->nombre() * $etiquette->price();
										$data = $ligne->enregistre();
									}
								}
							}
						}
					}
				}				
			}else{
				$data->status = false;
				$data->message = "La quantité (volume) totale contenues dans les emballages dépassent la quantité de production en stock, Veuillez vérifier les données !";
			}
		}else{
			$data->status = false;
			$data->message = "La quantite totale de <b>".$emballage->name()."</b> n'est pas suffisante pour effectuer ce conditionnement, Veuillez vérifier votre stock ou les données saisies!";
		}
	}else{
		$data->status = false;
		$data->message = "Vous ne disposez pas de suffisemment de <b>".$emballage->name()."</b> pour ce packaging, Veuillez vérifier les données !";
	}

	echo json_encode($data);
}