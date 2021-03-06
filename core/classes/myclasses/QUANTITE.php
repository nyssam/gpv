<?php
namespace Home;
use Native\RESPONSE;/**
 * 
 */
class QUANTITE extends TABLE
{

	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	public $name;
	public $isActive = TABLE::OUI;

	public function enregistre(){
		$data = new RESPONSE;
		if ($this->name != "") {
			$data = $this->save();

			if ($data->status) {
				foreach (PARFUM::findBy(["isActive ="=>TABLE::OUI]) as $key => $parfum) {
					foreach (TYPEPRODUIT::findBy(["isActive ="=>TABLE::OUI]) as $key => $type) {
						$ligne = new PRODUIT();
						$ligne->quantite_id = $this->id;
						$ligne->parfum_id = $parfum->id;
						$ligne->typeproduit_id = $type->id;
						$ligne->prix = 200;
						$ligne->prix_gros = 200;
						$ligne->enregistre();
					}
				}
			}
			
		}else{
			$data->status = false;
			$data->message = "Veuillez renseigner le nom de la quantité !";
		}
		return $data;
	}


	public function name(){
		return $this->name."L";
	}


	public function production(string $date1 = "2020-06-01", string $date2){
		$requette = "SELECT SUM(production) as production  FROM production, ligneproduction, prixdevente, quantite WHERE ligneproduction.produit_id = prixdevente.id AND ligneproduction.production_id = production.id AND prixdevente.quantite_id = quantite.id AND quantite.id = ? AND production.etat_id != ? AND DATE(ligneproduction.created) >= ? AND DATE(ligneproduction.created) <= ? GROUP BY quantite.id";
		$item = LIGNEPRODUCTION::execute($requette, [$this->id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNEPRODUCTION()]; }
		return $item[0]->production;
	}



	public function enBoutique(string $date){
		$total = 0;
		foreach ($this->fourni("prixdevente") as $key => $value) {
			$total += $value->totalMiseEnBoutique("2020-06-01", $date) - ($value->enProspection($date) + $value->livree("2020-06-01", $date) + $value->vendu("2020-06-01", $date) + $value->perte("2020-06-01", $date));
		}
		return $total;
	}


	public function enEntrepot(string $date){
		$total = 0;
		foreach ($this->fourni("prixdevente") as $key => $value) {
			$total += intval($value->stock) + $value->production("2020-06-01", $date) - $value->totalMiseEnBoutique("2020-06-01", $date);
		}
		return $total;
	}


	public function livree(string $date1 = "2020-06-01", string $date2){
		$total = 0;
		foreach ($this->fourni("prixdevente") as $key => $value) {
			$total += $value->livree($date1, $date2);
		}
		return $total;
	}



	public function perte(string $date1 = "2020-06-01", string $date2){
		$total = 0;
		foreach ($this->fourni("prixdevente") as $key => $value) {
			$total += $value->perte($date1, $date2);
		}
		return $total;
	}


	public function vendu(string $date1 = "2020-06-01", string $date2, int $boutique_id = null){
		$total = 0;
		if ($boutique_id == null) {
			foreach ($this->fourni("prixdevente") as $key => $value) {
				$total += $value->vendu($date1, $date2);
			}
		}else{
			foreach ($this->fourni("prixdevente") as $key => $value) {
				$total += $value->vendu($date1, $date2, $boutique_id);
			}
		}
		return $total;
	}



	public function stockGlobal(){
		return $this->enBoutique(dateAjoute()) + $this->enEntrepot(dateAjoute());
	}

	public function montantStock(){
		return $this->stockGlobal() * $this->price;;
	}


	public function montantVendu(string $date1 = "2020-06-01", string $date2){
		$total = 0;
		foreach ($this->fourni("prixdevente") as $key => $value) {
			$value->actualise();
			return ($value->vendu($date1, $date2) + $value->livree($date1, $date2)) * $value->prix->price;
		}
	}


	
	public function sentenseCreate(){
		return $this->sentense = "Ajout d'une nouvelle quantité : $this->name dans les paramétrages";
	}
	public function sentenseUpdate(){
		return $this->sentense = "Modification des informations de la quantité $this->id : $this->name ";
	}
	public function sentenseDelete(){
		return $this->sentense = "Suppression definitive de la quantité $this->id : $this->name";
	}


}
?>