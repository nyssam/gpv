<?php
namespace Home;
use Native\RESPONSE;

/**
 * 
 */
class MISEENBOUTIQUE extends TABLE
{
	
	
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	public $reference;
	public $employe_id;
	public $boutique_id;
	public $entrepot_id;
	public $datereception;
	public $employe_id_reception;
	public $employe_id_accepter;

	public $nom_livreur;
	public $contact_livreur;

	public $etat_id = ETAT::ENCOURS;
	public $comment;


	public function enregistre(){
		$data = new RESPONSE;
		$datas = BOUTIQUE::findBy(["id ="=>$this->boutique_id]);
		if (count($datas) == 1) {
			$datas = ENTREPOT::findBy(["id ="=>$this->entrepot_id]);
			if (count($datas) == 1) {
				$this->reference = "MEB/".date('dmY')."-".strtoupper(substr(uniqid(), 5, 6));
				$this->employe_id = getSession("employe_connecte_id");
				$data = $this->save();				
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



	public function valider(){
		$data = new RESPONSE;
		if ($this->etat_id == ETAT::ENCOURS) {
			$this->etat_id = ETAT::VALIDEE;
			$this->datereception = date("Y-m-d H:i:s");
			$this->employe_id_reception = getSession("employe_connecte_id");
			$this->historique("La mise en boutique en reference $this->reference vient d'être receptionné !");
			$data = $this->save();
		}else{
			$data->status = false;
			$data->message = "Vous ne pouvez plus faire cette opération sur cette mise en boutique !";
		}
		return $data;
	}


	public function accepter(){
		$data = new RESPONSE;
		if ($this->etat_id == ETAT::PARTIEL) {
			$this->etat_id = ETAT::ENCOURS;
			$this->employe_id_accepter = getSession("employe_connecte_id");
			$this->historique("La demande de mise en boutique en reference $this->reference vient d'être accepté !");
			$data = $this->save();
		}else{
			$data->status = false;
			$data->message = "Vous ne pouvez plus faire cette opération sur cette mise en boutique !";
		}
		return $data;
	}






	public function sentenseCreate(){
		return $this->sentense = "enregistrement d'une nouvelle mise en boutique N°$this->reference";
	}
	public function sentenseUpdate(){
		return $this->sentense = "Modification des informations de la mise en boutique N°$this->reference ";
	}
	public function sentenseDelete(){
		return $this->sentense = "Suppression definitive de la mise en boutique N°$this->reference";
	}
}

?>