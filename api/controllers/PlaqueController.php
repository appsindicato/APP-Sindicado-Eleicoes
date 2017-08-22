<?php
/**
* Controller responsável pelas chapas
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class PlaqueController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,election_id,plaque_type_id,name,valid,number,core_id';

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
        'search' => array('id', 'name', 'election_id', 'plaque_type_id', 'name','core_id','number'),
        'partials' => array('id', 'name', 'election_id', 'plaque_type_id', 'name')
    );
}