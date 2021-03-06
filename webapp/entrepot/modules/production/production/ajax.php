<?php 
namespace Home;
require '../../../../../core/root/includes.php';

use Native\RESPONSE;
use Native\ROOTER;
$params = PARAMS::findLastId();
$data = new RESPONSE;
extract($_POST);

$rooter = new ROOTER;
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($action == "calcul") {
	$datas = TYPEPRODUIT_PARFUM::findBy(["id ="=> $id]);
	if (count($datas) == 1) {
		$type = $datas[0];
		if (intval($type->ressource_id) > 0) {
			$type->actualise();
			?>
			<div class="row justify-content-center">
				<?php 
				if ($params->productionAuto == TABLE::OUI) {
					foreach ($type->fourni("exigenceproduction") as $key1 => $exi) {
						$res = $exi->fourni("ligneexigenceproduction", ["ressource_id ="=> $type->ressource_id])[0];
						$total = $val * $exi->quantite / $res->quantite;
						$datas = $exi->fourni("ligneexigenceproduction", ["ressource_id !="=> $type->ressource_id, "quantite >"=>0]);
						foreach ($datas as $key2 => $ligne) { 
							$ligne->actualise();
							?>
							<div class="col-sm text-center border-right">
								<label>Quantité de <?= $ligne->ressource->name()  ?></label>
								<h4 class="mp0"><?= round((($total * $ligne->quantite) / $exi->quantite), 2) ?> <?= $ligne->ressource->abbr  ?></h4>
							</div>	
						<?php }
					}
				} ?>
			</div><br><br><br>

			<div class="text-center">
				<h4>Pour une production total de </h4>
				<h2 class="gras mp0"><?= round($total, 1) ?> <?= $type->typeproduit->abbr  ?></h2>
			</div>
			<?php
		}else{ ?>
			<br><br>
			<h2 class="text-center"> Aucune matière première de base n'a été définie pour ce produit. Veuillez le faire dans les parametres de production pour pouvoir effectuer la production !</h2>
		<?php }
	}
}





if ($action == "nouvelleProduction") {
	$tab = [];
	$total = 0;
	$tests = $listeproduits = explode(",", $listeproduits);
	foreach ($tests as $key => $value) {
		$test = true;
		$lot = explode("-", $value);
		$id = $lot[0];
		$qte = end($lot);
		if ($qte > 0) {
			if ($params->productionAuto == TABLE::OUI) {
				$datas = TYPEPRODUIT_PARFUM::findBy(["id ="=> $id]);
				if (count($datas) == 1) {
					$type = $datas[0];
					if (intval($type->ressource_id) > 0) {
						$datas = $type->fourni("exigenceproduction");
						if (count($datas) == 1) {
							$exi = $datas[0];
							$res = $exi->fourni("ligneexigenceproduction", ["ressource_id ="=> $type->ressource_id])[0];
							$total = $qte * $exi->quantite / $res->quantite;
							$datas = $exi->fourni("ligneexigenceproduction", ["quantite >"=>0]);
							foreach ($datas as $key2 => $ligne) {
								if ($ligne->quantite > 0) {
									$ligne->actualise();
									$ressource = $ligne->ressource;
									if ($ligne->ressource->isActive() && ($total*$ligne->quantite/$exi->quantite) > $ligne->ressource->stock(PARAMS::DATE_DEFAULT, dateAjoute(1), getSession("entrepot_connecte_id")) ) {
										$test = false;
										break;
									}
								}
							}
						}else {
							$test = false;
							break ;
						}
					}else{
						$test = false;
						break ;
					}
				}
			}else{
				$total = $qte;
				foreach (RESSOURCE::getAll() as $key2 => $ressource) {
					if (isset($_POST["conso-".$ressource->id]) && $_POST["conso-".$ressource->id] > 0) {
						$tab[$ressource->id] = $_POST["conso-".$ressource->id];
						if ($ressource->isActive() && ($_POST["conso-".$ressource->id] > $ressource->stock(PARAMS::DATE_DEFAULT, dateAjoute(1), getSession("entrepot_connecte_id")))) {
							$test = false;
							break ;
						}
					}
				}
			}
		}
		if ($test) {
			unset($tests[$key]);
		}
	}
	if (count($tests) == 0) {
		if ($total > 0 && !($params->productionAuto == TABLE::NON && count($tab) <= 0)) {
			$datas = ENTREPOT::findBy(["id ="=>getSession("entrepot_connecte_id")]);
			if (count($datas) == 1) {
				$entrepot = $datas[0];
				$entrepot->actualise();
				if ($entrepot->comptebanque->solde() >= $maindoeuvre) {
					$production = new PRODUCTION();
					$production->hydrater($_POST);
					$data = $production->enregistre();
					if ($data->status) {
						foreach ($listeproduits as $key => $value) {
							$lot = explode("-", $value);
							$id = $lot[0];
							$qte = end($lot);

							$datas = TYPEPRODUIT_PARFUM::findBy(["id ="=> $id]);
							if (count($datas) == 1) {
								$type = $datas[0];
								$ligne = new LIGNEPRODUCTION();
								$ligne->production_id = $production->id;
								$ligne->typeproduit_parfum_id = $type->id;
								$ligne->quantite = intval($total);
								$data = $ligne->enregistre();

								if ($data->status) {
									if ($params->productionAuto == TABLE::OUI) {
										foreach ($type->fourni("exigenceproduction") as $key1 => $exi) {
											foreach ($exi->fourni("ligneexigenceproduction") as $key2 => $lign) {
												if ($lign->quantite > 0) {
													$lign->actualise();
													$ligne = new LIGNECONSOMMATION();
													$ligne->production_id = $production->id;
													$ligne->ressource_id = $lign->ressource->id;
													$ligne->quantite = $total*$lign->quantite/$exi->quantite;
													$ligne->price = $ligne->quantite * $lign->ressource->price();
													$data = $ligne->enregistre();
												}
											}
										}
									}else{
										foreach ($tab as $key2 => $value) {
											$ressource = new RESSOURCE;
											$ressource->id = $key2;
											$ressource->actualise();

											$ligne = new LIGNECONSOMMATION();
											$ligne->production_id = $production->id;
											$ligne->ressource_id = $key2;
											$ligne->quantite = $value;
											$ligne->price = $value * $ressource->price();
											$data = $ligne->enregistre();
										}
									}
								}

							}
						}
					}
				}else{
					$data->status = false;
					$data->message = "Le solde du compte est insuffisant pour regler les frais de main d'oeuvre de la production !";
				}
			}else{
				$data->status = false;
				$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !";
			}
		}else{
			$data->status = false;
			$data->message = "Veuillez renseigner les bonnes quantités pour définir la production !";
		}
	}else{
		$data->status = false;
		$data->message = "Vous n'avez pas assez ressources de <b>($ressource->name)</b> pour effectuer cette production !";
	}
	echo json_encode($data);
}








if ($action == "annulerProduction") {
	$datas = EMPLOYE::findBy(["id = "=>getSession("employe_connecte_id")]);
	if (count($datas) > 0) {
		$employe = $datas[0];
		$employe->actualise();
		if ($employe->checkPassword($password)) {
			$datas = PRODUCTION::findBy(["id ="=>$id]);
			if (count($datas) == 1) {
				$production = $datas[0];
				$data = $production->annuler();
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

