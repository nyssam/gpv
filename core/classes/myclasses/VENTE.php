<?php
namespace Home;
use Native\RESPONSE;/**
 * 
 */
class VENTE extends TABLE
{

	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	public $reference;
	public $typevente_id;
	public $groupecommande_id  = null;
	public $zonedevente_id;
	public $boutique_id = BOUTIQUE::PRINCIPAL;
	public $typebareme_id = TYPEBAREME::NORMAL;
	public $commercial_id      = COMMERCIAL::MAGASIN;
	public $etat_id            = ETAT::ENCOURS;
	public $employe_id         = null;
	public $reglementclient_id = null;
	public $sousTVA = TABLE::OUI;

	public $montant            = 0;
	public $rendu              = 0;
	public $recu               = 0;
	public $comment;

	

	public function enregistre(){
		$data = new RESPONSE;
		$datas = ZONEDEVENTE::findBy(["id ="=>$this->zonedevente_id]);
		if (count($datas) == 1) {
			$params = PARAMS::findLastId();


			$this->employe_id = getSession("employe_connecte_id");
			$this->boutique_id = getSession("boutique_connecte_id");
			$this->reference = "BVE/".date('dmY')."-".strtoupper(substr(uniqid(), 5, 6));
			$this->taux_tva = $params->tva;

			$this->rendu = $this->recu - $this->montant;
			if ($this->rendu < 0) {
				$this->rendu = 0;
			}
			$data = $this->save();
		}else{
			$data->status = false;
			$data->message = "Une erreur s'est produite lors de l'enregistrement de la vente!";
		}
		return $data;
	}



	public function payement(int $montant, Array $post){
		$this->actualise();
		$payement = new REGLEMENTCLIENT();
		$payement->hydrater($post);
		$payement->name = "Réglement de vente";
		$payement->montant = ceil($montant);
		$payement->comment = "Réglement de la ".$this->typevente->name()." N°".$this->reference;
		$payement->files = [];
		$payement->setId(null);
		$data = $payement->enregistre();
		if ($data->status) {
			$this->reglementclient_id = $data->lastid;
			$data = $this->save();
		}
		return $data;
	}

	public static function todayDirect(int $boutique_id){
		return static::findBy(["boutique_id ="=>$boutique_id, "DATE(created) ="=>dateAjoute(), "typevente_id="=>TYPEVENTE::DIRECT, "etat_id !="=>ETAT::ANNULEE]);
	}





	public function chauffeur(){
		if ($this->vehicule_id == VEHICULE::AUTO) {
			return "...";
		}else if ($this->vehicule_id == VEHICULE::TRICYCLE) {
			return $this->nom_tricycle;
		}else{
			return $this->chauffeur->name();
		}
	}


	public function vehicule(){
		if ($this->vehicule_id == VEHICULE::AUTO) {
			return "SON PROPRE VEHICULE";
		}else if ($this->vehicule_id == VEHICULE::TRICYCLE) {
			return "TRICYCLE";
		}else{
			return $this->vehicule->name();
		}
	}



	public function annuler(){
		$data = new RESPONSE;
		if ($this->etat_id != ETAT::ANNULEE) {
			$this->etat_id = ETAT::ANNULEE;
			$this->historique("La vente en reference $this->reference vient d'être annulée !");
			$data = $this->save();
			if ($data->status) {
				$this->actualise();
				$this->operation->annuler();
			}
		}else{
			$data->status = false;
			$data->message = "Vous ne pouvez plus faire cette opération sur cette vente !";
		}
		return $data;
	}



	public function terminer(){
		$data = new RESPONSE;
		if ($this->etat_id == ETAT::ENCOURS) {
			$this->etat_id = ETAT::VALIDEE;
			$this->dateretour = date("Y-m-d H:i:s");
			$this->historique("La vente en reference $this->reference vient d'être terminé !");
			$data = $this->save();
			if ($data->status) {
				$this->actualise();
				if ($this->chauffeur_id > 0) {
					$this->chauffeur->etatchauffeur_id = ETATCHAUFFEUR::RAS;
					$this->chauffeur->save();
				}

				$this->vehicule->etatvehicule_id = ETATVEHICULE::RAS;
				$this->vehicule->save();

				$this->groupecommande->etat_id = ETAT::ENCOURS;
				$this->groupecommande->save();
			}
		}else{
			$data->status = false;
			$data->message = "Vous ne pouvez plus faire cette opération sur cette vente !";
		}
		return $data;
	}



	public function montant(){
		$total = 0;
		$datas = $this->fourni("lignedevente");
		foreach ($datas as $key => $ligne) {
			$ligne->actualise();
			$total += $ligne->prixdevente->prix->price * $ligne->quantite;
		}
		return $total;
	}


	public function payer(int $montant, Array $post){
		$data = new RESPONSE;
		$solde = $this->reste;
		if ($solde > 0) {
			if ($solde >= $montant) {
				$payement = new OPERATION();
				$payement->hydrater($post);
				if ($payement->modepayement_id != MODEPAYEMENT::PRELEVEMENT_ACOMPTE) {
					$payement->categorieoperation_id = CATEGORIEOPERATION::PAYE_TRICYLE;
					$payement->manoeuvre_id = $this->id;
					$payement->comment = "Réglement de la paye de tricycle ".$this->chauffeur()." pour la commande N°".$this->reference;
					$data = $payement->enregistre();
					if ($data->status) {
						$this->reste -= $montant;
						$this->isPayer = 1;
						$data = $this->save();
					}
				}else{
					$data->status = false;
					$data->message = "Vous ne pouvez pas utiliser ce mode de payement pour effectuer cette opération !";
				}
			}else{
				$data->status = false;
				$data->message = "Le montant à verser est plus élévé que sa paye !";
			}
		}else{
			$data->status = false;
			$data->message = "Vous etes déjà à jour pour la paye de ce tricycle !";
		}
		return $data;
	}


	public static function direct(string $date1, string $date2, int $boutique_id = null){
		if ($boutique_id == null) {
			return static::findBy(["typevente_id ="=>TYPEVENTE::DIRECT, "DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE]);
		}else{
			return static::findBy(["typevente_id ="=>TYPEVENTE::DIRECT, "DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE, "boutique_id ="=>$boutique_id]);

		}
	}

	public static function prospection(string $date1, string $date2, int $boutique_id = null){
		if ($boutique_id == null) {
			return static::findBy(["typevente_id ="=>TYPEVENTE::PROSPECTION, "DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE]);
		}else{
			return static::findBy(["typevente_id ="=>TYPEVENTE::PROSPECTION, "DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE, "boutique_id ="=>$boutique_id]);

		}
	}


	public static function cave(string $date1, string $date2, int $boutique_id = null){
		if ($boutique_id == null) {
			return static::findBy(["typevente_id ="=>TYPEVENTE::VENTECAVE, "DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE]);
		}else{
			return static::findBy(["typevente_id ="=>TYPEVENTE::VENTECAVE, "DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE, "boutique_id ="=>$boutique_id]);

		}
	}


	public static function commande(string $date1, string $date2, int $boutique_id = null){
		if ($boutique_id == null) {
			return COMMANDE::findBy(["DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE]);
		}else{
			return COMMANDE::findBy(["DATE(created) >="=>$date1, "DATE(created) <="=>$date2, "etat_id !="=>ETAT::ANNULEE, "boutique_id ="=>$boutique_id]);

		}
	}


	public static function entree(string $date1 = "2020-04-01", string $date2){
		$requette = "SELECT SUM(montant) as montant  FROM operation, categorieoperation WHERE operation.categorieoperation_id = categorieoperation.id AND categorieoperation.typeoperationcaisse_id = ? AND operation.valide = 1 AND DATE(operation.created) >= ? AND DATE(operation.created) <= ?";
		$item = OPERATION::execute($requette, [TYPEOPERATIONCAISSE::ENTREE, $date1, $date2]);
		if (count($item) < 1) {$item = [new OPERATION()]; }
		return $item[0]->montant;
	}


	public static function ResetProgramme(){

	}


	public static function stats(string $date1 = "2020-04-01", string $date2, int $boutique_id = null){
		$tableaux = [];
		$nb = ceil(dateDiffe($date1, $date2) / 12);
		$index = $date1;
		if ($boutique_id == null) {
			while ( $index <= $date2 ) {
				
				$data = new \stdclass;
				$data->year = date("Y", strtotime($index));
				$data->month = date("m", strtotime($index));
				$data->day = date("d", strtotime($index));
				$data->nb = $nb;
			////////////

				$data->total = comptage(VENTE::findBy(["created >= "=>$date1, "created <= "=>$index]), "montant", "somme");
				$data->direct = comptage(VENTE::direct($date1, $index), "montant", "somme");
				$data->prospection = comptage(VENTE::prospection($date1, $index), "montant", "somme");
				$data->cave = comptage(VENTE::cave($date1, $index), "montant", "somme");
				// $data->marge = 0 ;

				$tableaux[] = $data;
			///////////////////////

				$index = dateAjoute1($index, ceil($nb));
			}
		}else{
			while ( $index <= $date2 ) {

				$data = new \stdclass;
				$data->year = date("Y", strtotime($index));
				$data->month = date("m", strtotime($index));
				$data->day = date("d", strtotime($index));
				$data->nb = $nb;
			////////////

				$data->total = comptage(VENTE::findBy(["created >= "=>$date1, "created <= "=>$index, "boutique_id ="=>$boutique_id]), "montant", "somme");
				$data->direct = comptage(VENTE::direct($date1, $index, $boutique_id), "montant", "somme");
				$data->prospection = comptage(VENTE::prospection($date1, $index, $boutique_id), "montant", "somme");
				$data->cave = comptage(VENTE::cave($date1, $index, $boutique_id), "montant", "somme");
				// $data->marge = 0 ;

				$tableaux[] = $data;
			///////////////////////

				$index = dateAjoute1($index, ceil($nb));
			}
		}
		return $tableaux;
	}




	public static function stats2(string $date1 = "2020-04-01", string $date2, int $boutique_id = null){
		$tableaux = [];
		$nb = ceil(dateDiffe($date1, $date2) / 12);
		$index = $date1;
		if ($boutique_id == null) {
			while ( $index <= $date2 ) {
				
				$data = new \stdclass;
				$data->year = date("Y", strtotime($index));
				$data->month = date("m", strtotime($index));
				$data->day = date("d", strtotime($index));
				$data->nb = $nb;
			////////////

				$data->total = comptage(VENTE::findBy(["created >= "=>$index, "created <= "=>$index]), "montant", "somme");
				$data->direct = comptage(VENTE::direct($index, $index), "montant", "somme");
				$data->prospection = comptage(VENTE::prospection($index, $index), "montant", "somme");
				$data->cave = comptage(VENTE::cave($index, $index), "montant", "somme");
				// $data->marge = 0 ;

				$tableaux[] = $data;
			///////////////////////

				$index = dateAjoute1($index, ceil($nb));
			}
		}else{
			while ( $index <= $date2 ) {

				$data = new \stdclass;
				$data->year = date("Y", strtotime($index));
				$data->month = date("m", strtotime($index));
				$data->day = date("d", strtotime($index));
				$data->nb = $nb;
			////////////

				$data->total = comptage(VENTE::findBy(["created >= "=>$index, "created <= "=>$index, "boutique_id ="=>$boutique_id]), "montant", "somme");
				$data->direct = comptage(VENTE::direct($index, $index, $boutique_id), "montant", "somme");
				$data->prospection = comptage(VENTE::prospection($index, $index, $boutique_id), "montant", "somme");
				$data->cave = comptage(VENTE::cave($index, $index, $boutique_id), "montant", "somme");
				// $data->marge = 0 ;

				$tableaux[] = $data;
			///////////////////////

				$index = dateAjoute1($index, ceil($nb));
			}
		}
		return $tableaux;
	}


	public function sentenseCreate(){
		return $this->sentense = "enregistrement d'une nouvelle vente ".$this->typevente->name()." N°$this->reference";
	}
	public function sentenseUpdate(){
		return $this->sentense = "Modification des informations de la vente N°$this->reference ";
	}
	public function sentenseDelete(){
		return $this->sentense = "Suppression definitive de la vente N°$this->reference";
	}



}
?>