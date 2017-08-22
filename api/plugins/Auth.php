<?php


class Auth {

	public static function validate($user = null,$pwd = null,$app){
		if ($user && $pwd){
			$login = User::findFirst(array(
						"conditions" => "email=?1 AND password=?2 AND valid=1",
						"bind" => array(1=>$user,2=>sha1($pwd))
			),false);
			if ($login){
				// $login->last_login = time();
				// $login->skipRequest = true;
				// $login->update();
				$app->session->set('id',$login->id);
				if($login->role == 1){
					$app->session->set('level',10);	
				}else{
					$app->session->set('level',1);
				}
				
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}	
	}

}


?>