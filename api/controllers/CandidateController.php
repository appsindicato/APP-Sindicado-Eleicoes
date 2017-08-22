<?php
/**
* Controller responsável pelos candidatos
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class CandidateController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,email,first_name,last_name,document,election_id,candidate_office_id,valid';
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
        'search' => array('id','email','first_name', 'last_name', "document","candidate_office_id","email"),
        'partials' => array('id','email','first_name', 'last_name', "document")
    );
}