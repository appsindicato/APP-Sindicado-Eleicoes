<?php
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class AclPlugin extends Plugin
{
	/**
	 * Returns an existing or new access control list
	 *
	 * @returns AclList
	 */
	public function getAcl()
	{
			$acl = new AclList();
			$acl->setDefaultAction(Acl::DENY);
			//Register roles
			$roles = [
				'public'  => new Role('Public'),
				'cliente' => new Role('Client'),
				'administrador' => new Role('Admin')
			];
			
			foreach ($roles as $role) {
				$acl->addRole($role);
			}
			$acl->addInherit('Client','Public');
			$acl->addInherit('Admin','Client');


			$resources = [
			    'Public'=>[
			    	'Key' => ['keygen'],
			    	'Configure' => ['install','database','urna'],
			    	'Upload' => ['vote']
			    ],
			    'Client'=>[
			    	'Login' => ['get']
			    ],
			    'Admin'=>[
			    	'User'=>['post', 'put', 'get', 'delete', 'count'],
			    	'Election'=>['post', 'get', 'delete'],
			    	'CandidateOffice'=>['post', 'put', 'get', 'delete', 'count'],
			    	'Candidate'=>['post', 'put', 'get', 'delete', 'count'],
			    	'City'=>['get', 'import', 'count'],
			    	'CoreZone'=>['get', 'import', 'count'],
			    	'Core'=>['post', 'put', 'get', 'delete', 'import', 'count'],
			    	'PlaqueType'=>['post', 'put', 'get', 'delete'],
			    	'Plaque'=>['post', 'put', 'get', 'delete', 'count'],
			    	'PlaqueCandidate'=>['post', 'put', 'get', 'delete', 'count'],
			    	'Box'=>['post', 'put', 'get', 'delete', 'suspend', 'transit', 'count'],
			    	'Voter'=>['get', 'import', 'count'],
			    	'Login' => ['get'],
			    	'Upload' => ['post'] ,
			    	'Configure' => ['electionDate'],
			    	'Report' => ['report', 'adviser']
			    ]
			];
			foreach($resources as $resource){
			    foreach($resource as $controller=>$arrMethods){
			        $acl->addResource(new Phalcon\Acl\Resource($controller . 'Controller'),$arrMethods);
			    }
			}
			/*
			 * ACCESS
			 * */
			foreach ($acl->getRoles() as $objRole) {
			    $roleName = $objRole->getName();
			    //publico
			    foreach ($resources['Public'] as $resource => $method) {
			        $acl->allow($roleName,$resource . 'Controller',$method);
			    }

			    if($roleName == 'Client'){
			        foreach ($resources['Client'] as $resource => $method) {
			            $acl->allow($roleName,$resource . 'Controller',$method);
			        }
			    }

			    //admins
			    if($roleName == 'Admin'){
			        foreach ($resources['Admin'] as $resource => $method) {
			            $acl->allow($roleName,$resource . 'Controller',$method);
			        }
			    }
			}//The acl is stored in session, APC would be useful here too
			$this->persistent->acl = $acl;
			
		return $this->persistent->acl;
	}

	public function getPermission($app){
		$acl = $this->getAcl();
		$arrHandler = $app->getActiveHandler();
		$controller = str_replace('Controller\\','',get_class($arrHandler[0]));
		switch($app->session->get('level')){
			case 10: // Admin
				return $acl->isAllowed('Admin', $controller, $arrHandler[1]);
			break;
			case 1: // Usuario
				return $acl->isAllowed('Client', $controller, $arrHandler[1]);
			break;
			default : //Public
				return $acl->isAllowed('Public', $controller, $arrHandler[1]);
			break;
		}
	}
	
}

?>
