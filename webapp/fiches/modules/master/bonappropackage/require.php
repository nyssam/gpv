<?php 

namespace Home;

if ($this->id != null) {
	$datas = APPROPACKAGE::findBy(["id ="=> $this->id, 'etat_id !='=>ETAT::ANNULEE]);
	if (count($datas) > 0) {
		$appro = $datas[0];
		$appro->actualise();


		$datas = $appro->fourni("reglementfournisseur", ["montant = "=> $appro->avance, "DATE(created) ="=> date("Y-m-d", strtotime($appro->created))]);
		if (count($datas) > 0) {
			$reglement = $datas[0];
			$reglement->actualise();
		}
		
		$appro->fourni("ligneappropackage");

		$title = "GPV | Bon d'approvisionnement ";
		
	}else{
		header("Location: ../master/clients");
	}
}else{
	header("Location: ../master/clients");
}

?>