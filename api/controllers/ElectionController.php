<?php
/**
* Controller responsável pela eleição
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class ElectionController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,start,valid,finish';
    /**
    * Allowed methods 
    */
	public static $restricted = array(
        'get' => false,
        'put' => true,
        'post' => false,
        'delete' => true
    );
    /**
    * Allow Cache
    */
	protected $cacheable = false;
    /**
     * Allows search by and partial by fields
     */
    public static $_allow = array(
        'search' => array('id', 'start', 'finish', 'valid'),
        'partials' => array('id', 'start', 'finish', 'valid')
    );
    /**
    * @method Responsável por inativar a eleição
    */
    public function delete($id = null){
        if($id){
            $election = Election::FindFirst($id, false);
            if($election && $election->delete()){
                $election->user_id = $this->session->get('id');
                if($election->save()){
                    return ResponseHandler::delete($this,$election);                    
                }
            }
        }
        return ResponseHandler::delete($this,null);
    }
}