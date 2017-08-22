<?php
/**
* Controller responsável pela relação da chapa com o candidato
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class PlaqueCandidateController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'plaque_id,candidate_id,id';
    /**
    * Allowed methods 
    */
	public static $restricted = array(
        'get' => false,
        'put' => false,
        'post' => false,
        'delete' => false
    );
    /**
    * Allow Cache
    */
	protected $cacheable = false;
    /**
     * Allows search by and partial by fields
     */
    public static $_allow = array(
        'search' => array('plaque_id', 'candidate_id'),
        'partials' => array('plaque_id', 'candidate_id')
    );
}