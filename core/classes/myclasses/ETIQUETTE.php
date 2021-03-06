<?php
namespace Home;
use Native\RESPONSE;

/**
 * 
 */
class ETIQUETTE extends TABLE
{
	
	
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;


	public $produit_id;
	public $price = 0;
	public $stkAlert = 0;


	public function enregistre(){
		$data = new RESPONSE;
		$datas = PRODUIT::findBy(["id ="=>$this->produit_id]);
		if (count($datas) == 1) {
			$data = $this->save();

			foreach (ENTREPOT::getAll() as $key => $exi) {
				$ligne = new INITIALETIQUETTEENTREPOT();
				$ligne->entrepot_id = $exi->id;
				$ligne->etiquette_id = $this->id;
				$ligne->quantite = 0;
				$ligne->enregistre();
			}
		}else{
			$data->status = false;
			$data->message = "Une erreur s'est produite lors du prix !";
		}
		return $data;
	}



	public function name()
	{
		$this->actualise();
		if ($this->produit->id > 0) {
			return $this->produit->name();
		}
		return "";
	}

	public function stock(String $date1, String $date2, int $entrepot_id){
		$item = $this->fourni("initialetiquetteentrepot", ["entrepot_id ="=>$entrepot_id])[0];
		return $this->achat($date1, $date2, $entrepot_id) - $this->consommee($date1, $date2, $entrepot_id) - $this->consommee($date1, $date2, $entrepot_id) + $item->quantite;
	}


	public function consommee(String $date1, String $date2, int $entrepot_id = null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(ligneconsommationetiquette.quantite) as quantite  FROM ligneconsommationetiquette, conditionnement WHERE ligneconsommationetiquette.etiquette_id = ? AND ligneconsommationetiquette.conditionnement_id = conditionnement.id AND conditionnement.etat_id != ? AND DATE(ligneconsommationetiquette.created) >= ? AND DATE(ligneconsommationetiquette.created) <= ? $paras";
		$item = LIGNECONSOMMATIONETIQUETTE::execute($requette, [$this->id, ETAT::ANNULEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNECONSOMMATIONETIQUETTE()]; }
		return $item[0]->quantite;
	}


	public function achat(String $date1, String $date2, int $entrepot_id = null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(quantite_recu) as quantite  FROM ligneapproetiquette, etiquette, approetiquette WHERE ligneapproetiquette.etiquette_id = etiquette.id AND etiquette.id = ? AND ligneapproetiquette.approetiquette_id = approetiquette.id AND approetiquette.etat_id = ? AND DATE(approetiquette.created) >= ? AND DATE(approetiquette.created) <= ? $paras";
		$item = LIGNEAPPROETIQUETTE::execute($requette, [$this->id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new LIGNEAPPROETIQUETTE()]; }
		return $item[0]->quantite;
	}



	public function perte(string $date1, string $date2, int $entrepot_id = null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(quantite) as quantite  FROM perteentrepot WHERE perteentrepot.etiquette_id = ? AND  perteentrepot.etat_id = ? AND DATE(perteentrepot.created) >= ? AND DATE(perteentrepot.created) <= ? $paras ";
		$item = PERTEENTREPOT::execute($requette, [$this->id, ETAT::VALIDEE, $date1, $date2]);
		if (count($item) < 1) {$item = [new PERTEENTREPOT()]; }
		return $item[0]->quantite;
	}



	public function price(){
		$requette = "SELECT SUM(quantite_recu) as quantite, SUM(transport) as transport, SUM(ligneapproetiquette.price) as price FROM ligneapproetiquette, approetiquette WHERE ligneapproetiquette.etiquette_id = ? AND ligneapproetiquette.approetiquette_id = approetiquette.id AND approetiquette.etat_id = ? ";
		$datas = LIGNEAPPROETIQUETTE::execute($requette, [$this->id, ETAT::VALIDEE]);
		if (count($datas) < 1) {$datas = [new LIGNEAPPROETIQUETTE()]; }
		$item = $datas[0];

		$requette = "SELECT SUM(quantite_recu) as quantite FROM ligneapproetiquette, approetiquette WHERE ligneapproetiquette.approetiquette_id = approetiquette.id AND approetiquette.id IN (SELECT approetiquette_id FROM ligneapproetiquette WHERE ligneapproetiquette.etiquette_id = ? ) AND approetiquette.etat_id = ? ";
		$datas = LIGNEAPPROETIQUETTE::execute($requette, [$this->id, ETAT::VALIDEE]);
		if (count($datas) < 1) {$datas = [new LIGNEAPPROETIQUETTE()]; }
		$ligne = $datas[0];

		if ($item->quantite == 0) {
			return 0;
		}
		if (intval($this->price) <= 0) {
			$total = ($item->price / $item->quantite) + ($item->transport / $ligne->quantite);
			return $total;
		}
		return $this->price + ($item->transport / $ligne->quantite);
	}



	public static function rupture(){
		$datas = static::findBy(["isActive ="=>TABLE::OUI]);
		foreach ($datas as $key => $item) {
			if ($item->enEntrepot(dateAjoute()) > $item->stkAlert) {
				unset($datas[$key]);
			}
		}
		return $datas;
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
		$this->sentense = "enregistrement d'un nouvel etiquette ".$this->name();
	}
	public function sentenseUpdate(){
		$this->sentense = "Modification des informations de l'etiquette ".$this->name();
	}
	public function sentenseDelete(){
		$this->sentense = "Suppression de l'etiquette ".$this->name();
	}


}

?>