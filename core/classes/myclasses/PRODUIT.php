<?php
namespace Home;
use Native\RESPONSE;

/**
 * 
 */
class PRODUIT extends TABLE
{
	
	
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;


	public $typeproduit_parfum_id;
	public $quantite_id;
	public $isActive = TABLE::NON;


	public function enregistre(){
		$data = new RESPONSE;
		$datas = TYPEPRODUIT_PARFUM::findBy(["id ="=>$this->typeproduit_parfum_id]);
		if (count($datas) == 1) {
			$datas = QUANTITE::findBy(["id ="=>$this->quantite_id]);
			if (count($datas) == 1) {
				$data = $this->save();
				if ($data->status) {
					foreach (EMBALLAGE::getAll() as $key => $emballage) {
						$item = new PRICE;
						$item->produit_id = $this->id;
						$item->emballage_id = $emballage->id;
						$item->prix = 200;
						$item->prix_gros = 200;
						$item->enregistre();
					}


					foreach (BOUTIQUE::getAll() as $key => $exi) {
						foreach (EMBALLAGE::getAll() as $key => $emb) {
							$ligne = new INITIALPRODUITBOUTIQUE();
							$ligne->boutique_id = $exi->id;
							$ligne->emballage_id = $emb->id;
							$ligne->produit_id = $this->id;
							$ligne->quantite = 0;
							$ligne->enregistre();
						}
					}


					foreach (ENTREPOT::getAll() as $key => $exi) {
						foreach (EMBALLAGE::getAll() as $key => $emb) {
							$ligne = new INITIALPRODUITENTREPOT();
							$ligne->entrepot_id = $exi->id;
							$ligne->emballage_id = $emb->id;
							$ligne->produit_id = $this->id;
							$ligne->quantite = 0;
							$ligne->enregistre();
						}
					}

					$item = new ETIQUETTE;
					$item->produit_id = $this->id;
					$item->initial = 0;
					$item->enregistre();
				}
			}else{
				$data->status = false;
				$data->message = "Une erreur s'est produite lors du prix !";
			}
		}else{
			$data->status = false;
			$data->message = "Une erreur s'est produite lors du prix !";
		}
		return $data;
	}



	public function name(){
		$this->actualise();
		return $this->typeproduit_parfum->name()." : ".$this->quantite->name();
	}

	public function name2(){
		return $this->typeproduit_parfum->name()." <br> ".$this->quantite->name();
	}


	public function getListeEmballageProduit(){
		$this->actualise();
		$requette = "SELECT emballage.* FROM caracteristiqueemballage, emballage WHERE caracteristiqueemballage.emballage_id = emballage.id AND typeproduit_id IN ('', ?) AND parfum_id IN ('', ?) AND quantite_id IN ('', ?) AND emballage.valide = ?";
		return EMBALLAGE::execute($requette, [$this->typeproduit_parfum->typeproduit->id, $this->typeproduit_parfum->parfum->id, $this->quantite->id, TABLE::OUI]);
	}

	///////////////////////////////////////////////////////////////////////////////////////////


	public static function totalVendu($date1, $date2, int $boutique_id=null, int $typeproduit_id=null, int $parfum_id=null, $quantite_id=null){
		$paras = "";
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		if ($typeproduit_id != null) {
			$paras.= "AND typeproduit_parfum.typeproduit_id = $typeproduit_id ";
		}
		if ($parfum_id != null) {
			$paras.= "AND typeproduit_parfum.parfum_id = $parfum_id ";
		}
		if ($quantite_id != null) {
			$paras.= "AND quantite_id = $quantite_id ";
		}
		$paras.= " AND vente.created BETWEEN '$date1' AND '$date2'";
		$requette = "SELECT lignedevente.* FROM lignedevente, vente, produit, typeproduit_parfum WHERE lignedevente.vente_id = vente.id AND lignedevente.produit_id = produit.id AND produit.typeproduit_parfum_id = typeproduit_parfum.id $paras";
		$datas = LIGNEDEVENTE::execute($requette, []);
		return comptage($datas, "price", "somme");
	}



	public static function totalProduit($date1, $date2, int $entrepot_id=null, int $typeproduit_id=null, int $parfum_id=null){
		$paras = "";
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		if ($typeproduit_id != null) {
			$paras.= "AND typeproduit_parfum.typeproduit_id = $typeproduit_id ";
		}
		if ($parfum_id != null) {
			$paras.= "AND typeproduit_parfum.parfum_id = $parfum_id ";
		}
		$paras.= " AND production.created BETWEEN '$date1' AND '$date2'";
		$requette = "SELECT ligneproduction.* FROM ligneproduction, production, typeproduit_parfum WHERE ligneproduction.production_id = production.id AND ligneproduction.typeproduit_parfum_id = typeproduit_parfum.id $paras";
		$datas = LIGNEPRODUCTION::execute($requette, []);
		return comptage($datas, "quantite", "somme");
	}



	public static function totalConditionnement(string $date1, string $date2, int $entrepot_id = null, int $quantite_id = null, int $emballage_id=null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		if ($quantite_id != null) {
			$paras.= "AND quantite_id = $quantite_id ";
		}
		if ($emballage_id != null) {
			$paras.= "AND emballage_id = $emballage_id ";
		}
		$requette = "SELECT SUM(ligneconditionnement.quantite) as quantite  FROM conditionnement, ligneconditionnement WHERE ligneconditionnement.produit_id = ? AND ligneconditionnement.conditionnement_id = conditionnement.id AND conditionnement.etat_id != ? AND DATE(conditionnement.created) >= ? AND DATE(conditionnement.created) <= ? $paras";
		$item = LIGNECONDITIONNEMENT::execute($requette, [$this->id, $emballage_id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNECONDITIONNEMENT()]; }
		return $item[0]->quantite;
	}


	public function conditionnement(string $date1, string $date2, int $emballage_id, int $entrepot_id = null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(ligneconditionnement.quantite) as quantite  FROM conditionnement, ligneconditionnement WHERE ligneconditionnement.produit_id = ? AND ligneconditionnement.emballage_id = ? AND ligneconditionnement.conditionnement_id = conditionnement.id AND conditionnement.etat_id != ? AND DATE(conditionnement.created) >= ? AND DATE(conditionnement.created) <= ? $paras";
		$item = LIGNECONDITIONNEMENT::execute($requette, [$this->id, $emballage_id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNECONDITIONNEMENT()]; }
		return $item[0]->quantite;
	}


	public function totalSortieEntrepot(string $date1, string $date2, int $emballage_id, int $entrepot_id = null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM lignemiseenboutique, miseenboutique WHERE lignemiseenboutique.produit_id = ? AND lignemiseenboutique.emballage_id = ? AND lignemiseenboutique.miseenboutique_id = miseenboutique.id AND miseenboutique.etat_id != ?  AND DATE(lignemiseenboutique.created) >= ? AND DATE(lignemiseenboutique.created) <= ? $paras ";
		$item = LIGNEMISEENBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNEMISEENBOUTIQUE()]; }
		return $item[0]->quantite;
	}



	public function perteEntrepot(string $date1, string $date2, int $emballage_id, int $entrepot_id = null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM perteentrepot WHERE perteentrepot.produit_id = ? AND  perteentrepot.emballage_id = ? AND perteentrepot.etat_id = ? AND DATE(perteentrepot.created) >= ? AND DATE(perteentrepot.created) <= ? $paras ";
		$item = PERTEENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new PERTEENTREPOT()]; }
		return $item[0]->quantite;
	}


	public function transfertEntrepot(string $date1, string $date2, int $emballage_id, int $entrepot_id = null){
		$total = 0;
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(quantite1) as quantite  FROM transfertstockentrepot WHERE transfertstockentrepot.produit_id = ? AND  transfertstockentrepot.emballage_id_destination = ? AND transfertstockentrepot.etat_id = ? AND DATE(transfertstockentrepot.created) >= ? AND DATE(transfertstockentrepot.created) <= ? $paras ";
		$item = TRANSFERTSTOCKENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new TRANSFERTSTOCKENTREPOT()]; }
		$total = $item[0]->quantite;

		$requette = "SELECT SUM(quantite) as quantite  FROM transfertstockentrepot WHERE transfertstockentrepot.produit_id = ? AND  transfertstockentrepot.emballage_id_source = ? AND transfertstockentrepot.etat_id = ? AND DATE(transfertstockentrepot.created) >= ? AND DATE(transfertstockentrepot.created) <= ? $paras ";
		$item = TRANSFERTSTOCKENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new TRANSFERTSTOCKENTREPOT()]; }
		$total -= $item[0]->quantite;
		return $total;
	}


	public function retourStockEntrepot(string $date1, string $date2, int $emballage_id, int $entrepot_id = null){
		$total = 0;
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM retourstockentrepot WHERE retourstockentrepot.produit_id_source = ? AND  retourstockentrepot.emballage_id_source = ? AND retourstockentrepot.etat_id = ? AND DATE(retourstockentrepot.created) >= ? AND DATE(retourstockentrepot.created) <= ? $paras ";
		$item = RETOURSTOCKENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new RETOURSTOCKENTREPOT()]; }
		$total = $item[0]->quantite;

		$requette = "SELECT SUM(quantite1) as quantite  FROM retourstockentrepot WHERE retourstockentrepot.produit_id_destination = ? AND  retourstockentrepot.emballage_id_destination = ? AND retourstockentrepot.etat_id = ? AND DATE(retourstockentrepot.created) >= ? AND DATE(retourstockentrepot.created) <= ? $paras ";
		$item = RETOURSTOCKENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new RETOURSTOCKENTREPOT()]; }
		$total -= $item[0]->quantite;
		return $total;
	}



	public function reconditionner(string $date1, string $date2, int $emballage_id, int $entrepot_id = null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM reconditionnement WHERE reconditionnement.produit_id = ? AND reconditionnement.emballage_id = ? AND reconditionnement.etat_id != ? AND DATE(reconditionnement.created) >= ? AND DATE(reconditionnement.created) <= ? $paras ";
		$item = RECONDITIONNEMENT::execute($requette, [$this->id, $emballage_id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new RECONDITIONNEMENT()]; }
		return $item[0]->quantite;
	}



	public function Qtereconditionner(string $date1, string $date2, int $entrepot_id = null){
		$total = 0;
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT * FROM reconditionnement WHERE reconditionnement.produit_id = ? AND reconditionnement.etat_id != ? AND DATE(reconditionnement.created) >= ? AND DATE(reconditionnement.created) <= ? $paras ";
		$datas = RECONDITIONNEMENT::execute($requette, [$this->id, ETAT::ANNULEE, $date1, $date2]);
		foreach ($datas as $key => $recon) {
			$recon->actualise();
			$total += $recon->quantite * $recon->emballage->nombre() * $recon->produit->quantite->name ;
		}
		return $total;
	}



	public function enEntrepot(string $date1, string $date2, int $emballage_id, int $entrepot_id){
		$item = $this->fourni("initialproduitentrepot", ["emballage_id ="=>$emballage_id, "entrepot_id ="=>$entrepot_id])[0];
		$total = $this->conditionnement($date1, $date2, $emballage_id, $entrepot_id) 
		- $this->totalSortieEntrepot($date1, $date2, $emballage_id, $entrepot_id) 
		- $this->perteEntrepot($date1, $date2, $emballage_id, $entrepot_id) 
		- $this->reconditionner($date1, $date2, $emballage_id, $entrepot_id) 
		+ $this->retourStockEntrepot($date1, $date2, $emballage_id, $entrepot_id)
		+ $this->transfertEntrepot($date1, $date2, $emballage_id, $entrepot_id)
		+ $item->quantite ; 
		return $total;
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	public function totalMiseEnBoutique(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM lignemiseenboutique, miseenboutique WHERE lignemiseenboutique.produit_id = ? AND lignemiseenboutique.emballage_id = ? AND lignemiseenboutique.miseenboutique_id = miseenboutique.id AND miseenboutique.etat_id = ?  AND DATE(lignemiseenboutique.created) >= ? AND DATE(lignemiseenboutique.created) <= ? $paras ";
		$item = LIGNEMISEENBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNEMISEENBOUTIQUE()]; }
		return $item[0]->quantite;
	}


	public function perteBoutique(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM perteboutique WHERE perteboutique.produit_id = ? AND perteboutique.emballage_id = ? AND perteboutique.etat_id = ? AND DATE(perteboutique.created) >= ? AND DATE(perteboutique.created) <= ? $paras ";
		$item = PERTEENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new PERTEENTREPOT()]; }
		return $item[0]->quantite;
	}


	public function transfertBoutique(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$total = 0;
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite1) as quantite  FROM transfertstockboutique WHERE transfertstockboutique.produit_id = ? AND  transfertstockboutique.emballage_id_destination = ? AND transfertstockboutique.etat_id = ? AND DATE(transfertstockboutique.created) >= ? AND DATE(transfertstockboutique.created) <= ? $paras ";
		$item = TRANSFERTSTOCKBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new TRANSFERTSTOCKBOUTIQUE()]; }
		$total = $item[0]->quantite;

		$requette = "SELECT SUM(quantite) as quantite  FROM transfertstockboutique WHERE transfertstockboutique.produit_id = ? AND  transfertstockboutique.emballage_id_source = ? AND transfertstockboutique.etat_id = ? AND DATE(transfertstockboutique.created) >= ? AND DATE(transfertstockboutique.created) <= ? $paras ";
		$item = TRANSFERTSTOCKBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new TRANSFERTSTOCKBOUTIQUE()]; }
		$total -= $item[0]->quantite;
		return $total;
	}



	public function retourStockBoutique(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$total = 0;
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM retourstockboutique WHERE retourstockboutique.produit_id_source = ? AND  retourstockboutique.emballage_id_source = ? AND retourstockboutique.etat_id = ? AND DATE(retourstockboutique.created) >= ? AND DATE(retourstockboutique.created) <= ? $paras ";
		$item = RETOURSTOCKBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new RETOURSTOCKBOUTIQUE()]; }
		$total = $item[0]->quantite;

		$requette = "SELECT SUM(quantite1) as quantite  FROM retourstockboutique WHERE retourstockboutique.produit_id_destination = ? AND  retourstockboutique.emballage_id_destination = ? AND retourstockboutique.etat_id = ? AND DATE(retourstockboutique.created) >= ? AND DATE(retourstockboutique.created) <= ? $paras ";
		$item = RETOURSTOCKBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new RETOURSTOCKBOUTIQUE()]; }
		$total -= $item[0]->quantite;
		return $total;
	}


	public function retourStockEntrepot_boutique(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$total = 0;
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM retourstockentrepot WHERE retourstockentrepot.produit_id_source = ? AND  retourstockentrepot.emballage_id_source = ? AND retourstockentrepot.etat_id = ? AND DATE(retourstockentrepot.created) >= ? AND DATE(retourstockentrepot.created) <= ? $paras ";
		$item = RETOURSTOCKENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new RETOURSTOCKENTREPOT()]; }
		$total = -$item[0]->quantite;

		$requette = "SELECT SUM(quantite1) as quantite  FROM retourstockentrepot WHERE retourstockentrepot.produit_id_destination = ? AND  retourstockentrepot.emballage_id_destination = ? AND retourstockentrepot.etat_id = ? AND DATE(retourstockentrepot.created) >= ? AND DATE(retourstockentrepot.created) <= ? $paras ";
		$item = RETOURSTOCKENTREPOT::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new RETOURSTOCKENTREPOT()]; }
		$total += $item[0]->quantite;
		return $total;
	}

	public function transfertBoutiqueEntrant(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id_destination = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM lignetransfertboutique, transfertboutique WHERE lignetransfertboutique.produit_id = ? AND lignetransfertboutique.emballage_id = ? AND lignetransfertboutique.transfertboutique_id = transfertboutique.id AND transfertboutique.etat_id = ?  AND DATE(lignetransfertboutique.created) >= ? AND DATE(lignetransfertboutique.created) <= ? $paras ";
		$item = LIGNETRANSFERTBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNETRANSFERTBOUTIQUE()]; }
		return $item[0]->quantite;
	}


	public function transfertBoutiqueSortant(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM lignetransfertboutique, transfertboutique WHERE lignetransfertboutique.produit_id = ? AND lignetransfertboutique.emballage_id = ? AND lignetransfertboutique.transfertboutique_id = transfertboutique.id AND transfertboutique.etat_id != ?  AND DATE(lignetransfertboutique.created) >= ? AND DATE(lignetransfertboutique.created) <= ? $paras ";
		$item = LIGNETRANSFERTBOUTIQUE::execute($requette, [$this->id, $emballage_id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNETRANSFERTBOUTIQUE()]; }
		return $item[0]->quantite;
	}



	public function enBoutique(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$item = $this->fourni("initialproduitboutique", ["emballage_id ="=>$emballage_id, "boutique_id ="=>$boutique_id])[0];
		$total = 
		+ $item->quantite
		+ $this->totalMiseEnBoutique($date1, $date2, $emballage_id, $boutique_id) 
		+ $this->transfertBoutiqueEntrant($date1, $date2, $emballage_id, $boutique_id) 
		+ $this->transfertBoutique($date1, $date2, $emballage_id, $boutique_id) 
		+ $this->retourStockBoutique($date1, $date2, $emballage_id, $boutique_id) 
		+ $this->retourStockEntrepot_boutique($date1, $date2, $emballage_id, $boutique_id) 
		- $this->perteProspection($date1, $date2, $emballage_id, $boutique_id) 
		- $this->perteBoutique($date1, $date2, $emballage_id, $boutique_id) 
		- $this->vendu($date1, $date2, $emballage_id, $boutique_id) 
		- $this->livree($date1, $date2, $emballage_id, $boutique_id) 
		- $this->enProspection($emballage_id, $boutique_id) 
		- $this->transfertBoutiqueSortant($date1, $date2, $emballage_id, $boutique_id);
		return $total;
	}




	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



	public function enProspection(int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM ligneprospection, prospection WHERE ligneprospection.produit_id = ? AND ligneprospection.emballage_id = ? AND ligneprospection.prospection_id = prospection.id AND prospection.typeprospection_id = ? AND prospection.etat_id IN (?, ?)  $paras";
		$item = LIGNEPROSPECTION::execute($requette, [$this->id, $emballage_id, TYPEPROSPECTION::PROSPECTION, ETAT::ENCOURS, ETAT::PARTIEL]);
		if (count($item) < 1) {$item = [new LIGNEPROSPECTION()]; }
		return $item[0]->quantite;
	}



	public function livree(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite_vendu) as quantite  FROM ligneprospection, prospection WHERE ligneprospection.produit_id =  ? AND ligneprospection.emballage_id = ? AND ligneprospection.prospection_id = prospection.id AND prospection.typeprospection_id = ? AND prospection.etat_id != ? AND ligneprospection.created >= ? AND ligneprospection.created <= ? $paras ";
		$item = LIGNEPROSPECTION::execute($requette, [$this->id, $emballage_id, TYPEPROSPECTION::LIVRAISON, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNEPROSPECTION()]; }
		return $item[0]->quantite;
	}



	public function perteProspection(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(perte) as perte  FROM ligneprospection, prospection WHERE ligneprospection.produit_id = ? AND ligneprospection.emballage_id = ? AND ligneprospection.prospection_id = prospection.id AND prospection.etat_id != ? AND ligneprospection.created >= ? AND ligneprospection.created <= ? $paras ";
		$item = LIGNEPROSPECTION::execute($requette, [$this->id, $emballage_id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNEPROSPECTION()]; }
		return $item[0]->perte;
	}


	public function vendu(string $date1, string $date2, int $emballage_id, int $boutique_id = null){
		$paras = "";
		if ($boutique_id != null) {
			$paras.= "AND boutique_id = $boutique_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM lignedevente, vente WHERE lignedevente.produit_id = ? AND lignedevente.emballage_id = ? AND lignedevente.vente_id = vente.id AND vente.etat_id != ? AND DATE(lignedevente.created) >= ? AND DATE(lignedevente.created) <= ? $paras ";
		$item = LIGNEDEVENTE::execute($requette, [$this->id, $emballage_id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNEDEVENTE()]; }
		return $item[0]->quantite;
	}


	// public function vendeDirecte(string $date1, string $date2, int $boutique_id = null){
	// 	$paras = "";
	//if ($boutique_id != null) {
	// 		$paras.= "AND boutique_id = $boutique_id ";
	// 	}
	// 	$requette = "SELECT SUM(quantite) as quantite  FROM lignedevente, produit, vente WHERE lignedevente.produit_id = produit.id AND lignedevente.vente_id = vente.id AND produit.id = ? AND  vente.etat_id != ? AND vente.typevente_id = ? AND vente.boutique_id =? AND  DATE(lignedevente.created) >= ? AND DATE(lignedevente.created) <= ? ";
	// 	$item = LIGNEDEVENTE::execute($requette, [$this->id, ETAT::ANNULEE, TYPEVENTE::DIRECT, $boutique_id, $date1, $date2]);
	// 	if (count($item) < 1) {$item = [new LIGNEDEVENTE()]; }
	// 	$total += $item[0]->quantite;
	// 	return $total;
	// }


	// public function vendeProspection(string $date1, string $date2, int $boutique_id = null){
	// 	$total = 0;
	// 	$paras = "";
	//	if ($boutique_id != null) {
	// 		$requette = "SELECT SUM(quantite) as quantite  FROM lignedevente, produit, vente WHERE lignedevente.produit_id = produit.id AND lignedevente.vente_id = vente.id AND produit.id = ? AND  vente.etat_id != ? AND vente.boutique_id =?  AND vente.typevente_id = ? AND DATE(lignedevente.created) >= ? AND DATE(lignedevente.created) <= ? ";

	// 		$item = LIGNEDEVENTE::execute($requette, [$this->id, ETAT::ANNULEE, $boutique_id, TYPEVENTE::PROSPECTION, $date1, $date2]);
	// 		if (count($item) < 1) {$item = [new LIGNEDEVENTE()]; }
	// 		$total += $item[0]->quantite;
	// 	}else{
	// 		$requette = "SELECT SUM(quantite) as quantite  FROM lignedevente, produit, vente WHERE lignedevente.produit_id = produit.id AND lignedevente.vente_id = vente.id AND produit.id = ? AND  vente.etat_id != ? AND vente.typevente_id = ? AND DATE(lignedevente.created) >= ? AND DATE(lignedevente.created) <= ? ";

	// 		$item = LIGNEDEVENTE::execute($requette, [$this->id, ETAT::ANNULEE, TYPEVENTE::PROSPECTION, $date1, $date2]);
	// 		if (count($item) < 1) {$item = [new LIGNEDEVENTE()]; }
	// 		$total += $item[0]->quantite;
	// 	}
	// 	return $total;
	// }



	public function commandee(int $boutique_id = null){
		$total = 0;
		$datas = GROUPECOMMANDE::encours();
		foreach ($datas as $key => $comm) {
			if ($comm->boutique_id == $boutique_id) {
				$total += $comm->reste($this->id);
			}
		}
		return $total;
	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function stock(string $date){
		$total = 0;
		foreach (BOUTIQUE::getAll() as $key => $value) {
			$total += $this->enBoutique($date, $value->id);
		}
		foreach (ENTREPOT::getAll() as $key => $value) {
			$total += $this->enEntrepot($date, $value->id);
		}
		return $total;
	}


	public function montantStock(int $boutique_id = null){
		$this->actualise();
		if ($boutique_id == null) {
			return $this->enBoutique(dateAjoute()) * $this->prix->price;
		}else{
			return $this->enBoutique(dateAjoute(), $boutique_id) * $this->prix->price;
		}
	}


	public function montantVendu(string $date1, string $date2, int $boutique_id = null){
		$this->actualise();
		if ($boutique_id == null) {
			return ($this->vendu($date1, $date2) + $this->livree($date1, $date2) )* $this->prix->price ;
		}else{
			return ($this->vendu($date1, $date2, $boutique_id) + $this->livree($date1, $date2, $boutique_id) )* $this->prix->price ;
		}
	}



	public static function ruptureBoutique(int $boutique_id = null){
		$params = PARAMS::findLastId();
		$requette = "SELECT price.* FROM price, produit, typeproduit_parfum, quantite, emballage WHERE 
		produit.isActive = ? AND typeproduit_parfum.isActive = ? AND emballage.isActive = ? AND quantite.isActive = ? AND 
		price.produit_id = produit.id AND price.emballage_id = emballage.id AND produit.typeproduit_parfum_id = typeproduit_parfum.id AND produit.quantite_id = quantite.id ";
		$datas = PRICE::execute($requette, [TABLE::OUI, TABLE::OUI, TABLE::OUI, TABLE::OUI]);
		foreach ($datas as $key => $item) {
			$item->actualise();
			if ($item->produit->enBoutique(PARAMS::DATE_DEFAULT, dateAjoute(1), $item->emballage_id, $boutique_id) > $params->ruptureStock) {
				unset($datas[$key]);
			}
		}
		return $datas;
	}


	public static function ruptureEntrepot(int $entrepot_id = null){
		$params = PARAMS::findLastId();
		$requette = "SELECT price.* FROM price, produit, typeproduit_parfum, quantite, emballage WHERE 
		produit.isActive = ? AND typeproduit_parfum.isActive = ? AND emballage.isActive = ? AND quantite.isActive = ? AND 
		price.produit_id = produit.id AND price.emballage_id = emballage.id AND produit.typeproduit_parfum_id = typeproduit_parfum.id AND produit.quantite_id = quantite.id ";
		$datas = PRICE::execute($requette, [TABLE::OUI, TABLE::OUI, TABLE::OUI, TABLE::OUI]);
		foreach ($datas as $key => $item) {
			$item->actualise();
			if ($item->produit->enEntrepot(PARAMS::DATE_DEFAULT, dateAjoute(1), $item->emballage_id, $entrepot_id) > $params->ruptureStock) {
				unset($datas[$key]);
			}
		}
		return $datas;
	}



	public function exigence(int $quantite, int $ressource_id){
		$datas = EXIGENCEPRODUCTION::findBy(["produit_id ="=>$this->id, "ressource_id ="=>$ressource_id]);
		if (count($datas) == 1) {
			$item = $datas[0];
			if ($item->quantite_produit == 0) {
				return 0;
			}
			return ($quantite * $item->quantite_ressource) / $item->quantite_produit;
		}
		return 0;
	}



	public function coutProduction(String $type, int $quantite){
		if(isJourFerie(dateAjoute())){
			$datas = PAYEFERIE_PRODUIT::findBy(["produit_id ="=>$this->id]);
		}else{
			$datas = PAYE_PRODUIT::findBy(["produit_id ="=>$this->id]);
		}
		if (count($datas) > 0) {
			$ppr = $datas[0];
			switch ($type) {
				case 'production':
				$prix = $ppr->price;
				break;

				case 'rangement':
				$prix = $ppr->price_rangement;
				break;

				case 'vente':
				$prix = $ppr->price_vente;
				break;

				default:
				$prix = $ppr->price;
				break;
			}
			return $quantite * $prix;
		}
		return 0;
	}



	public function changerMode(){
		if ($this->isActive == TABLE::OUI) {
			$this->isActive = TABLE::NON;
		}else{
			$this->isActive = TABLE::OUI;
			$pro = PRODUCTION::today();
			$datas = LIGNEPRODUCTION::findBy(["production_id ="=>$pro->id, "produit_id ="=>$pdv->id]);
			if (count($datas) == 0) {
				$ligne = new LIGNEPRODUCTION();
				$ligne->production_id = $pro->id;
				$ligne->produit_id = $pdv->id;
				$ligne->enregistre();
			}			
		}
		return $this->save();
	}



	public function sentenseCreate(){
		$this->sentense = "enregistrement d'un nouveau produit ".$this->name();
	}
	public function sentenseUpdate(){
		$this->sentense = "Modification des informations du produit ".$this->name();
	}
	public function sentenseDelete(){
		$this->sentense = "Suppression du produit ".$this->name();
	}

}

?>