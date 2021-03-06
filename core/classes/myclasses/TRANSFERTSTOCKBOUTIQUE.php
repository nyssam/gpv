<?php
namespace Home;
use Native\RESPONSE;
use Native\EMAIL;
/**
 * 
 */
class TRANSFERTSTOCKBOUTIQUE extends TABLE
{
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;

	/* cette classe n'est liée à aucune table; elle ne sert que d'interface pour les operations de tranfert de fonds */

	public $produit_id;
	public $quantite;
	public $emballage_id_source;
	public $quantite1;
	public $emballage_id_destination;
	public $comment;
	public $boutique_id;
	public $employe_id;
	public $etat_id = ETAT::VALIDEE;



	public function enregistre(){
		$data = new RESPONSE;
		$this->employe_id = getSession("employe_connecte_id");
		$this->boutique_id = getSession("boutique_connecte_id");
		$this->produit_id = getSession("produit_id");
		$datas = PRODUIT::findBy(["id ="=>$this->produit_id]);
		if (count($datas) == 1) {
			$produit = $datas[0];
			$datas = EMBALLAGE::findBy(["id ="=>$this->emballage_id_source]);
			if (count($datas) == 1) {
				$emb1 = $datas[0];
				$datas = EMBALLAGE::findBy(["id ="=>$this->emballage_id_destination]);
				if (count($datas) == 1) {
					$emb2 = $datas[0];
					if ($this->emballage_id_source != $this->emballage_id_destination){
						if ($this->quantite >= 1){
							if ($produit->enBoutique(PARAMS::DATE_DEFAULT, dateAjoute(1), $this->emballage_id_source, getSession("boutique_connecte_id")) >= $this->quantite) {
								$this->quantite1 = (int)($this->quantite * $emb1->nombre() / $emb2->nombre());
								if ($this->quantite1 >= 1){
									$data = $this->save();	
								}else{
									$data->status = false;
									$data->message = "la quantité à transferer est insuffisant !";
								}
							}
						}else{
							$data->status = false;
							$data->message = "Veuillez vérifier la quantité à transferer, veuillez recommencer !!";
						}
					}else{
						$data->status = false;
						$data->message = "Veuillez verifier l'emballage de destination !!";
					}
				}else{
					$data->status = false;
					$data->message = "Veuillez vérifier la quantité à transferer, veuillez recommencer !!";
				}
			}else{
				$data->status = false;
				$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !!";
			}
		}else{
			$data->status = false;
			$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !!";
		}
		return $data;
	}



	public function sentenseCreate(){
		return $this->sentense = "Nouveau transfert de stock à partir de la boutique ".$this->boutique->name();
	}
	public function sentenseUpdate(){
		return $this->sentense = "Modification des informations du transfert de stock $this->id ";
	}
	public function sentenseDelete(){
		return $this->sentense = "Suppression definitive du transfert de stock $this->id";
	}

}



?>