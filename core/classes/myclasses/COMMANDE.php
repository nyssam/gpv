<?php
namespace Home;
use Native\RESPONSE;/**
 * 
 */
class COMMANDE extends TABLE
{

	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	public $reference;
	public $typecommande_id;
	public $code;
	public $groupecommande_id;
	public $datelivraison;
	public $boutique_id;
	public $zonedevente_id;
	public $typebareme_id;
	public $lieu;
	public $taux_tva = 0;
	public $tva = 0;
	public $montant = 0;
	public $avance = 0;
	public $reste = 0;
	public $isRegle = TABLE::NON;
	public $sousTVA = TABLE::OUI;
	public $etat_id = ETAT::VALIDEE;
	public $employe_id;
	public $comment;

	public $acompteClient = 0;
	public $detteClient = 0;
	


	public function enregistre(){
		$data = new RESPONSE;
		if ($this->datelivraison >= dateAjoute()) {
			if ($this->lieu != "") {
				$datas = ZONEDEVENTE::findBy(["id ="=>$this->zonedevente_id]);
				if (count($datas) == 1) {
					$params = PARAMS::findLastId();

					$this->employe_id = getSession("employe_connecte_id");
					$this->boutique_id = getSession("boutique_connecte_id");

					$this->reference = "BCO/".date('dmY')."-".strtoupper(substr(uniqid(), 5, 6));
					$this->taux_tva = $params->tva;

					$data = $this->save();
				}else{
					$data->status = false;
					$data->message = "Une erreur s'est produite lors de l'enregistrement de la commande!";
				}
			}else{
				$data->status = false;
				$data->message = "veuillez indiquer la destination précise de la commande *!";
			}
		}else{
			$data->status = false;
			$data->message = "La date de commande de la commande n'est pas correcte *!";
		}
		return $data;
	}



	public function reste(){
		return $this->montant - comptage($this->fourni("reglementclient"), "montant", "somme");
	}



	public function reglementDeCommande(){
		$data = new RESPONSE;
		$this->actualise();
		if ($this->reste() > 0) {
			if ($this->groupecommande->client->acompte > 0) {
				$reglementclient = new REGLEMENTCLIENT();
				$reglementclient->recouvrement = TABLE::OUI;
				$reglementclient->commande_id = $this->id;
				$reglementclient->client_id = $this->groupecommande->client_id;
				$reglementclient->montant = ($this->groupecommande->client->acompte >= $this->reste())? $this->reste() : $this->groupecommande->client->acompte;
				$reglementclient->modepayement_id = MODEPAYEMENT::PRELEVEMENT_ACOMPTE;
				$reglementclient->comment = "Recouvrement de commande N°$this->reference ";
				$reglementclient->historique("Recouvrement de commande N°$this->reference pour un montant de $reglementclient->montant ");
				$data = $reglementclient->enregistre();
				if ($data->status) {
					$data = $this->groupecommande->client->debiter($reglementclient->montant);
				}
			}else{
				$data->status = false;
				$data->message = "L'acompte du client est épuisé pour recouvrir la/les commandes restante(s) !!";
			}
		}else{
			$data->status = false;
			$data->message = "Cette commande à déjà été réglé entièrement !";
		}
		return $data ;
	}



	public function annuler(){
		$data = new RESPONSE;
		if ($this->etat_id != ETAT::ANNULEE) {
			$this->actualise();
			$datas = $this->fourni("lignecommande");
			$test = true;
			foreach ($datas as $key => $ligne) {
				if ($ligne->quantite > $this->groupecommande->reste($ligne->produit_id, $ligne->emballage_id)) {
					$test = false;
					break;
				}
			}
			if ($test) {
				$this->etat_id = ETAT::ANNULEE;
				$this->historique("La commande en reference $this->reference vient d'être annulée !");
				$data = $this->save();
				if ($data->status) {
					$this->actualise();
					if ($this->operation_id > 0) {
						$this->operation->supprime();
						$this->groupecommande->client->dette -= $this->montant - $this->avance;
						$this->groupecommande->client->save();
					}else{
						//paymenet par prelevement banquaire
						$this->groupecommande->client->acompte += $this->avance;
						$this->groupecommande->client->dette -= $this->montant - $this->avance;
						$this->groupecommande->client->save();
					}

					$this->groupecommande->etat_id = ETAT::ENCOURS;
					$this->groupecommande->save();
				}
			}else{
				$data->status = false;
				$data->message = "Cette commande a déjà été entamé, il n'est donc plus possible de pouvour la supprimer !";
			}
		}else{
			$data->status = false;
			$data->message = "Vous ne pouvez plus faire cette opération sur cette commande !";
		}
		return $data;
	}




	public static function CA(string $date1 = "2020-04-01", string $date2){
		$datas = static::findBy(["etat_id !="=>ETAT::ANNULEE, "DATE(created) >="=>$date1, "DATE(created) <="=>$date2]);
		return comptage($datas, "montant", "somme");
	}



	public function sentenseCreate(){
		return $this->sentense = "enregistrement d'une nouvelle commande N°$this->reference";
	}
	public function sentenseUpdate(){
		return $this->sentense = "Modification des informations de la commande N°$this->reference ";
	}
	public function sentenseDelete(){
		return $this->sentense = "Suppression definitive de la commande N°$this->reference";
	}

}
?>