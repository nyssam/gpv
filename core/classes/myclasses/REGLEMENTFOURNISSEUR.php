<?php
namespace Home;
use Native\RESPONSE;
use Native\EMAIL;
/**
 * 
 */
class REGLEMENTFOURNISSEUR extends TABLE
{
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	public $reference;
	public $mouvement_id;
	public $fournisseur_id;
	public $comment;
	public $etat_id = ETAT::VALIDEE;
	public $approvisionnement_id;
	public $approemballage_id;
	public $appropackage_id;
	public $approetiquette_id;
	public $modepayement_id;
	public $entrepot_id;
	public $structure;
	public $numero;
	public $date_approbation;
	public $isModified = 0;
	public $employe_id;

	public $recouvrement;

	public $image;
	public $montant;
	public $comptebanque_id;

	public $idd;
	public $classe;


	public function enregistre(){
		$data = new RESPONSE;
		if (isset($this->recouvrement) && $this->recouvrement == TABLE::OUI) {
			$data = $this->recouvrement();
		}else{
			$this->employe_id = getSession("employe_connecte_id");
			$this->entrepot_id = getSession("entrepot_connecte_id");
			$datas = EMPLOYE::findBy(["id ="=>$this->employe_id]);
			if (count($datas) == 1) {
				$this->reference = "RGF/".date('dmY')."-".strtoupper(substr(uniqid(), 5, 6));
				if (!in_array($this->modepayement_id, [MODEPAYEMENT::ESPECE, MODEPAYEMENT::PRELEVEMENT_ACOMPTE])) {
					$this->etat_id = ETAT::ENCOURS;
				}else{
					$this->etat_id = ETAT::VALIDEE;
				}

				if (intval($this->montant) > 0) {
					$datas = ENTREPOT::findBy(["id ="=>getSession("entrepot_connecte_id")]);
					if (count($datas) == 1) {
						$entrepot = $datas[0];
						$entrepot->actualise();
						if ($entrepot->comptebanque->solde() >= $this->montant) {
							$mouvement = new MOUVEMENT();
							$mouvement->name = "reglement de fournisseur";
							$mouvement->montant = $this->montant;
							$mouvement->comment = $this->comment;
							$mouvement->modepayement_id = $this->modepayement_id;
							$mouvement->typemouvement_id = TYPEMOUVEMENT::RETRAIT;
							$mouvement->comptebanque_id  = $entrepot->comptebanque_id;
							$data = $mouvement->enregistre();
							if ($data->status) {
								$this->mouvement_id = $mouvement->id;
								$data = $this->save();
								if ($data->status) {
									if (!(isset($this->files) && is_array($this->files))) {
										$this->files = [];
									}
									$this->uploading($this->files);
								}
							}					
						}else{
							$data->status = false;
							$data->message = "Le solde du compte est insuffisant pour effectuer cette opération !!";
						}
					}else{
						$data->status = false;
						$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !!";
					}
				}else{
					$data->status = false;
					$data->message = "Le montant pour cette opération est incorrecte, verifiez-le !";
				}
			}else{
				$data->status = false;
				$data->message = "++Une erreur s'est produite lors de l'opération, veuillez recommencer !!";
			}
		}
		return $data;
	}



	public function recouvrement(){
		$data = new RESPONSE;
		$datas = (TABLE::fullyClassName($this->classe))::findBy(["id = "=>$this->idd]);
		if (count($datas) > 0) {
			$appro = $datas[0];
			if ($appro->reste() >= $this->montant) {

				$this->employe_id = getSession("employe_connecte_id");
				$this->entrepot_id = getSession("entrepot_connecte_id");
				$this->reference = "RGC/".date('dmY')."-".strtoupper(substr(uniqid(), 5, 6));

				if (!in_array($this->modepayement_id, [MODEPAYEMENT::ESPECE, MODEPAYEMENT::PRELEVEMENT_ACOMPTE])) {
					$this->etat_id = ETAT::ENCOURS;
				}

				if ($this->modepayement_id != MODEPAYEMENT::PRELEVEMENT_ACOMPTE) {
					if (intval($this->montant) > 0) {
						$datas = ENTREPOT::findBy(["id ="=>getSession("entrepot_connecte_id")]);
						if (count($datas) == 1) {
							$entrepot = $datas[0];
							$entrepot->actualise();
							if ($entrepot->comptebanque->solde() >= $this->montant) {
								$mouvement = new MOUVEMENT();
								$mouvement->name = "reglement de fournisseur";
								$mouvement->montant = $this->montant;
								$mouvement->comment = $this->comment;
								$mouvement->modepayement_id = $this->modepayement_id;
								$mouvement->typemouvement_id = TYPEMOUVEMENT::RETRAIT;
								$mouvement->comptebanque_id  = $entrepot->comptebanque_id;
								$data = $mouvement->enregistre();
								if ($data->status) {
									$this->mouvement_id = $mouvement->id;
									$data = $this->save();
									if ($data->status) {
										if (!(isset($this->files) && is_array($this->files))) {
											$this->files = [];
										}
										$this->uploading($this->files);
									}
								}
							}else{
								$data->status = false;
								$data->message = "Le solde du compte est insuffisant pour effectuer cette opération !!";
							}
						}else{
							$data->status = false;
							$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !!";
						}
					}else{
						$data->status = false;
						$data->message = "Le montant pour cette opération est incorrecte, verifiez-le !";
					}
				}else{
					$data->status = false;
					$data->message = "Vous ne pouvez pas utiliser ce mode de payement !";
				}
			}else{
				$data->status = false;
				$data->message = "Le montant saisi est supérieur au reste à recouvrir !";
			}
		}else{
			$data->status = false;
			$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !!";
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
						$result = $image->upload("images", "operations", $a);
						$name = $tab[$i];
						$this->$name = $result->filename;
						$this->save();
					}
				}	
				$i++;			
			}			
		}
	}


	public function valider(){
		$data = new RESPONSE;
		$this->etat_id = ETAT::VALIDEE;
		$this->date_approbation = date("Y-m-d H:i:s");
		$this->historique("Approbation de l'opération de caisse N° $this->reference");
		return $this->save();
	}


	public function annuler(){
		return $this->supprime();
	}



	public static function total(string $date1 = "2020-04-01", string $date2, int $entrepot_id =null){
		$paras = "";
		if ($entrepot_id != null) {
			$paras.= "AND entrepot_id = $entrepot_id ";
		}
		$requette = "SELECT SUM(reglementfournisseur.montant) as montant  FROM reglementfournisseur, mouvement WHERE reglementfournisseur.mouvement_id = mouvement.id AND mouvement.typemouvement_id = ? AND reglementfournisseur.valide = 1 AND DATE(reglementfournisseur.created) >= ? AND DATE(reglementfournisseur.created) <= ? $paras ";
		$item = MOUVEMENT::execute($requette, [TYPEMOUVEMENT::RETRAIT, $date1, $date2]);
		if (count($item) < 1) {$item = [new MOUVEMENT()]; }
		return $item[0]->montant;
	}



	public static function enAttente(){
		return static::findBy(["etat_id ="=> ETAT::ENCOURS]);
	}



	public static function statistiques(){
		$tableau_mois = ["", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
		$tableau_mois_abbr = ["", "Jan", "Fév", "Mar", "Avr", "Mai", "Jui", "Juil", "Août", "Sept", "Oct", "Nov", "Déc"];
		$mois1 = date("m", strtotime("-1 year")); $year1 = date("Y", strtotime("-1 year"));
		$mois2 = date("m"); $year2 = date("Y");
		$tableaux = [];
		while ( $year2 >= $year1) {
			$debut = $year1."-".$mois1."-01";
			$fin = $year1."-".$mois1."-".cal_days_in_month(CAL_GREGORIAN, ($mois1), $year1);
			$data = new RESPONSE;
			$data->name = $tableau_mois_abbr[intval($mois1)]." ".$year1;
			//$data->name = $year1."-".start0($mois1)."-".cal_days_in_month(CAL_GREGORIAN, ($mois1), $year1);;
			////////////

			$data->entree = OPERATION::entree($debut, $fin);
			$data->sortie = OPERATION::sortie($debut, $fin);
			$data->resultat = OPERATION::resultat($debut, $fin);

			$tableaux[] = $data;
			///////////////////////
			if ($mois2 == $mois1 && $year2 == $year1) {
				break;
			}else{
				if ($mois1 == 12) {
					$mois1 = 01;
					$year1++;
				}else{
					$mois1++;
				}
			}
		}
		return $tableaux;
	}



	public static function stats(string $date1 = "2020-04-01", string $date2){
		$tableaux = [];
		$nb = ceil(dateDiffe($date1, $date2) / 12);
		$index = $date1;
		while ( $index <= $date2 ) {
			$debut = $index;
			$fin = dateAjoute1($index, ceil($nb/2));

			$data = new \stdclass;
			$data->year = date("Y", strtotime($index));
			$data->month = date("m", strtotime($index));
			$data->day = date("d", strtotime($index));
			$data->nb = $nb;
			////////////

			$data->ca = OPERATION::entree($debut, $fin);
			$data->sortie = OPERATION::sortie($debut, $fin);
			$data->marge = 0 ;
			if ($data->ca != 0) {
				$data->marge = (OPERATION::resultat($debut, $fin) / $data->ca) *100;
			}

			$tableaux[] = $data;
			///////////////////////

			$index = $fin;
		}
		return $tableaux;
	}


	public function sentenseCreate(){
		$this->sentense = "Nouveau reglement de fournisseur  N°$this->reference pour ".$this->fournisseur ->name()." d'un montant de $this->montant";
	}
	public function sentenseUpdate(){
		$this->sentense = "Modification des informations du reglement de fournisseur  N°$this->reference ";
	}
	public function sentenseDelete(){
		$this->sentense = "Nouveau reglement de fournisseur  N°$this->reference pour ".$this->fournisseur ->name()." d'un montant de $this->montant";
	}

}



?>