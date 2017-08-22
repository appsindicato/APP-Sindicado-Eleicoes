<?php
/**
* Controller responsável pelos tipos de chapas
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class PlaqueTypeController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,name,valid';
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
        'search' => array('id', 'name'),
        'partials' => array('id', 'name')
    );
}