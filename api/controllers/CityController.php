<?php
/**
* Controller responsável pelas chapas
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
class CityController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,name,state,valid';
    /**
    * Allowed methods 
    */
	public static $restricted = [
        'get' => false,
        'put' => true,
        'post' => true,
        'delete' => true
    ];
    /**
    * Allow Cache
    */
	protected $cacheable = false;
    /**
     * Allows search by and partial by fields
     */
    public static $_allow = [
        'search' => ['id', 'name', 'state', 'valid'],
        'partials' => ['id', 'name', 'state', 'valid']
    ];
    /**
    * @method Responsável por importar as cidades
    */
    public function import(){
        try{
            set_time_limit (240);
            $request = $this->di->get('request')->getJsonRawBody();
            if(!$request->filename){
                return ResponseHandler::post($this, null, null, ['error'=> ['controller.city.filename.notfound']]);
            }
            //conversar com PH + BK sobre upload para aws e ver plugin front do BK            
            //@todo: mover o arquivo do S3 para pasta local
            $object = ImportPlugin::import($request->filename, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');

            if($object->quantity === 0){
                return ResponseHandler::post($this, null, null, ['error' => $object->content]);
            }  
            // Create a transaction manager
            $manager = new TxManager();

            // Request a transaction 
            $transaction = $manager->get();

            $stack_trace = [];

            if($object && $object->quantity > 0){
                foreach($object->content as $o){
                    $city = new City();
                    $city->id = $o->id;
                    $city->name = $o->name;
                    $city->state = $o->state;
                    $city->valid = 1;
                    if(!$city->save()){
                        foreach($city->getMessages() as $m){
                            $stack_trace[] = $m->getMessage();
                        }
                    }
                }
                if(count($stack_trace)){
                    $transaction->rollback('something happen');
                }else{
                    $transaction->commit();
                    $link = LogPlugin::export('success', User::findFirst($this->di->get('session')->id), $start_time, 'Cidades', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
                    return ResponseHandler::post($this, ['quantity' => $object->quantity]);
                }
            }
        }catch (TxFailed $e){
            $link = LogPlugin::export('error', User::findFirst($this->di->get('session')->id), $start_time, 'Cidades', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
            return ResponseHandler::post($this, false, false, ['errors' => $stack_trace]);
        } 
    }
}