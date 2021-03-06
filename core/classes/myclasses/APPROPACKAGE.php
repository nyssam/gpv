<?php
namespace Home;
use Native\RESPONSE;
use Native\EMAIL;
/**
 * 
 */
class APPROPACKAGE extends TABLE
{
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	public $reference;
	public $montant = 0;
	public $avance = 0;
	public $reste = 0;
	public $transport = 0;
	public $fournisseur_id;
	public $entrepot_id;
	public $employe_id;
	public $etat_id;
	public $employe_reception;
	public $comment;
	public $datelivraison;

	public $acompteFournisseur = 0;
	public $detteFournisseur = 0;
	

	public function enregistre(){
		$data = new RESPONSE;
		$datas = FOURNISSEUR::findBy(["id ="=>$this->fournisseur_id]);
		if (count($datas) == 1) {
			if ($this->montant >= 0 ) {
				$this->reference = "APP/".date('dmY')."-".strtoupper(substr(uniqid(), 5, 6));
				$this->employe_id = getSession("employe_connecte_id");
				$this->entrepot_id = getSession("entrepot_connecte_id");
				$data = $this->save();
				if ($data->status && $this->transport > 0) {
					$mouvement = new OPERATION();
					$mouvement->montant = $this->transport;
					$mouvement->categorieoperation_id = CATEGORIEOPERATION::FRAISTRANSPORT;
					$mouvement->modepayement_id = MODEPAYEMENT::ESPECE;
					$mouvement->comment = "Frais de transport pour l'approvisionnement d'emballage N° ".$this->reference;
					$data = $mouvement->enregistre();
				}
			}else{
				$data->status = false;
				$data->message = "Le montant de la commande n'est pas correcte !";
			}
		}else{
			$data->status = false;
			$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !";
		}
		return $data;
	}


	public function reste(){
		return $this->montant - comptage($this->fourni("reglementfournisseur"), "montant", "somme");
	}
	

	public function reglement(){
		$data = new RESPONSE;
		$this->actualise();
		if ($this->reste() > 0) {
			$solde = $this->entrepot->comptebanque->solde();
			if ($this->entrepot->comptebanque->solde() > 0) {
				$reglement = new REGLEMENTFOURNISSEUR();
				$reglement->recouvrement = TABLE::OUI;
				$reglement->idd = $this->id;
				$reglement->appropackage_id = $this->id;
				$reglement->classe = "appropackage";
				$reglement->fournisseur_id = $this->fournisseur->id;
				$reglement->montant = ($solde >= $this->reste())? $this->reste() : $solde;
				$reglement->modepayement_id = MODEPAYEMENT::ESPECE;
				$reglement->comment = "Recouvrement d'approvisionnement d'extras d'emballages N°$this->reference ";
				$reglement->historique("Recouvrement d'approvisionnement d'extras d'emballages N°$this->reference pour un montant de $reglement->montant ");
				$data = $reglement->enregistre();
			}else{
				$data->status = false;
				$data->message = "L'acompte du fournisseur est épuisé pour recouvrir la/les approvisionnements restante(s) !!";
			}
		}else{
			$data->status = false;
			$data->message = "Cet approvisionnement à déjà été réglé entièrement !";
		}
		return $data ;
	}


	

	public function annuler(){
		$data = new RESPONSE;
		if ($this->etat_id != ETAT::ANNULEE) {
			$this->etat_id = ETAT::ANNULEE;
			$this->datelivraison = date("Y-m-d H:i:s");
			$this->historique("L'approvisionnement en reference $this->reference vient d'être annulée !");
			$data = $this->save();
			if ($data->status) {
				$this->actualise();
				$datas = $this->fourni("reglementfournisseur");
				if (count($datas) > 0) {
					foreach ($datas as $key => $reglement) {
						$reglement->supprime();
						// $this->fournisseur->dette -= $reglement->montant;
						// $this->fournisseur->save();
					}
				}else{
					//paymenet par prelevement banquaire
					$this->fournisseur->acompte += $this->avance;
					// $this->fournisseur->dette -= $this->montant - $this->avance;
					// $this->fournisseur->save();
				}
			}
		}else{
			$data->status = false;
			$data->message = "Vous ne pouvez plus faire cette opération sur cet approvisionnement !";
		}
		return $data;
	}



	public function terminer(){
		$data = new RESPONSE;
		if ($this->etat_id == ETAT::ENCOURS) {
			$this->etat_id = ETAT::VALIDEE;
			$this->employe_reception = getSession("employe_connecte_id");
			$this->datelivraison = date("Y-m-d H:i:s");
			$this->historique("L'approvisionnement en reference $this->reference vient d'être terminé !");
			$data = $this->save();
		}else{
			$data->status = false;
			$data->message = "Vous ne pouvez plus faire cette opération sur cet approvisionnement !";
		}
		return $data;
	}


	
	public function sentenseCreate(){
		$this->sentense = "enregistrement d'un nouvel approvisionnement d'extras d'emballage N°$this->reference ";
	}
	public function sentenseUpdate(){
		$this->sentense = "Modification des informations de l'approvisionnement d'extras d'emballage N°$this->reference ";
	}
	public function sentenseDelete(){
		$this->sentense = "Suppression de l'approvisionnement d'extras d'emballage N°$this->reference ";
	}

}



?>