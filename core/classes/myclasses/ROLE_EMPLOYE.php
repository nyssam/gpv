<?php
namespace Home;
use Native\RESPONSE;

/**
 * 
 */
class ROLE_EMPLOYE extends TABLE
{
	
	
	public static $tableName = __CLASS__;
	public static $namespace = __NAMESPACE__;


	public $role_id;
	public $employe_id;


	public function enregistre(){
		$data = new RESPONSE;
		$datas = ROLE::findBy(["id ="=>$this->role_id]);
		if (count($datas) == 1) {
			$datas = EMPLOYE::findBy(["id ="=>$this->employe_id]);
			if (count($datas) == 1) {
				$datas = static::findBy(["employe_id ="=>$this->employe_id, "role_id ="=>$this->role_id,]);
			if (count($datas) == 0) {
				$data = $this->save();
			}else{
				$data->status = false;
				$data->message = "Vous avez déjà un accès à cette fonctionnalité !";
			}
			}else{
				$data->status = false;
				$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !";
			}
		}else{
			$data->status = false;
			$data->message = "Une erreur s'est produite lors de l'opération, veuillez recommencer !";
		}
		return $data;
	}



	public function sentenseCreate(){
		return $this->sentense = "Attribution du rôle ".$this->role->name()." à ".$this->employe->name();
	}
	public function sentenseUpdate(){
		return $this->sentense = "Modification du du rôle ".$this->role->name()." à ".$this->employe->name();
	}
	public function sentenseDelete(){
		return $this->sentense = "Suppression du rôle ".$this->role->name()." à ".$this->employe->name();
	}
}

?>