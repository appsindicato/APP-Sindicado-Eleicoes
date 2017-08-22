<?php
/**
* Controller responsável pelos usuário
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class UserController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,email,first_name,last_name,role,document';
    /**
    * Allowed methods 
    */
	public static $restricted = array(
        'get' => false,
        'put' => true,
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
        'search' => array('id','email','first_name','last_name','role'),
        'partials' => array('id','email')
    );
}