<?php
/**
* Controller responsável pelo login
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class LoginController extends \Phalcon\Mvc\Controller
{
	/**
    * @method Responsável pelo login
    */
    public function get(){
        return ResponseHandler::get($this,[
            'id' => $this->session->get('id')
        ]);
    }

}
