<?php
namespace Home;
use Native\RESPONSE;
use Native\EMAIL;
use Native\FICHIER;
/**
 * 
 */
class PARFUM extends TABLE
{
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	public $name;
	public $couleur;
	public $isActive = TABLE::OUI;


	public function enregistre(){
		$data = new RESPONSE;
		if ($this->name != "") {
			$data = $this->save();
			if ($data->status) {
				foreach (TYPEPRODUIT::getAll() as $key => $type) {
					$ligne = new TYPEPRODUIT_PARFUM();
					$ligne->parfum_id = $this->id;
					$ligne->typeproduit_id = $type->id;
					$ligne->enregistre();
				}
			}
		}else{
			$data->status = false;
			$data->message = "Veuillez renseigner le nom du produit !";
		}
		return $data;
	}




	public function uploading(Array $files){
		//les proprites d'images;
		$tab = ["image"];
		if (is_array($files) && count($files) > 0) {
			$i = 0;
			foreach ($files as $key => $file) {
				if ($file["tmp_name"] != "") {
					$image = new FICHIER();
					$image->hydrater($file);
					if ($image->is_image()) {
						$a = substr(uniqid(), 5);
						$result = $image->upload("images", "produits", $a);
						$name = $tab[$i];
						$this->$name = $result->filename;
						$this->save();
					}
				}	
				$i++;			
			}			
		}
	}


	public function quantiteProduite(string $date1 = "2020-06-01", string $date2, int $entrepot_id = null){
		if ($entrepot_id == null) {
			$requette = "SELECT SUM(quantite.name * ligneproduction.production) as name  FROM production, ligneproduction, prixdevente, quantite, produit WHERE ligneproduction.produit_id = prixdevente.id AND ligneproduction.production_id = production.id AND prixdevente.produit_id = produit.id AND prixdevente.quantite_id = quantite.id AND produit.id = ? AND production.etat_id != ? AND DATE(ligneproduction.created) >= ? AND DATE(ligneproduction.created) <= ? GROUP BY prixdevente.id";
			$item = QUANTITE::execute($requette, [$this->id, ETAT::ANNULEE, $date1, $date2]);
			if (count($item) < 1) {$item = [new QUANTITE()]; }
		}else{
			$requette = "SELECT SUM(quantite.name * ligneproduction.production) as name  FROM production, ligneproduction, prixdevente, quantite, produit WHERE ligneproduction.produit_id = prixdevente.id AND ligneproduction.production_id = production.id AND prixdevente.produit_id = produit.id AND prixdevente.quantite_id = quantite.id AND produit.id = ? AND production.etat_id != ? AND production.entrepot_id = ? AND DATE(ligneproduction.created) >= ? AND DATE(ligneproduction.created) <= ? GROUP BY prixdevente.id";
			$item = QUANTITE::execute($requette, [$this->id, ETAT::ANNULEE, $entrepot_id, $date1, $date2]);
			if (count($item) < 1) {$item = [new QUANTITE()]; }
		}

		return $item[0]->name;
	}



	public function vendu(string $date1 = "2020-06-01", string $date2, int $boutique_id = null){
		$total = 0;
		if ($boutique_id == null) {
			$requette = "SELECT SUM(quantite.name) as name FROM lignedevente, prixdevente, vente, quantite, produit WHERE lignedevente.produit_id = prixdevente.id AND lignedevente.vente_id = vente.id AND prixdevente.id = ? AND vente.etat_id != ? AND prixdevente.quantite_id = quantite.id AND prixdevente.produit_id = produit.id AND DATE(lignedevente.created) >= ? AND DATE(lignedevente.created) <= ? GROUP BY prixdevente.id";
			$item = QUANTITE::execute($requette, [$this->id, ETAT::ANNULEE, $date1, $date2]);
			if (count($item) < 1) {$item = [new QUANTITE()]; }
		}else{
			$requette = "SELECT SUM(quantite.name) as name FROM lignedevente, prixdevente, vente, quantite, produit WHERE lignedevente.produit_id = prixdevente.id AND lignedevente.vente_id = vente.id AND prixdevente.id = ? AND vente.etat_id != ? AND prixdevente.quantite_id = quantite.id AND prixdevente.produit_id = produit.id AND DATE(lignedevente.created) >= ? AND DATE(lignedevente.created) <= ? GROUP BY prixdevente.id";
			$item = QUANTITE::execute($requette, [$this->id, ETAT::ANNULEE, $date1, $date2]);
			if (count($item) < 1) {$item = [new QUANTITE()]; }
		}
		$total += $item[0]->name;

		return $total;
	}



	public function sentenseCreate(){
		return $this->sentense = "Creation d'un nouveau parfum : $this->name";
	}
	public function sentenseUpdate(){
		return $this->sentense = "Modification des informations de la parfum $this->id : $this->name ";
	}
	public function sentenseDelete(){
		return $this->sentense = "Suppression definitive de la parfum $this->id : $this->name";
	}

}



?>