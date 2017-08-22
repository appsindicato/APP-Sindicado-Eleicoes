<?php
use \Phalcon\Mvc\Micro\Collection AS Collection;

class MicroApi extends \Phalcon\Mvc\Micro
{
	 public function __construct($di){
	 	parent::__construct($di);
	 }
	 public function generateRoutes($r){
	 	foreach($r AS $prefix=>$p){
			$a = new Collection();
			$controller = Phalcon\Text::camelize($prefix)."Controller";
			if (class_exists($controller)){
				$a->setHandler( new $controller() );
				$a->setPrefix("/".$prefix);
		 		foreach($p AS $method=>$url){
		 			foreach($url AS $u=>$m){
		 				$a->$method($u,$m);
		 			}
		 		}
				$this->mount($a);
			}
	 	}
	 }
}