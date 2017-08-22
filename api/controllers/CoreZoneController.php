<?php
/**
* Controller responsável pelas cidades do núcleo
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
class CoreZoneController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,core_id,city_id,valid';
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
        'search' => ['id', 'core_id', 'city_id'],
        'partials' => ['id', 'core_id', 'city_id']
    ];
    /**
    * @method Responsável por importar as cidades dentro dos núcleos
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
            $i=0;
            if($object && $object->quantity > 0){
                foreach($object->content as $o){
                    $i++;
                    $core_zone = new CoreZone();
                    $core_zone->city_id = $o->city_id;
                    $core_zone->core_id = $o->core_id;

                    if(City::findFirst($o->city_id)){
                        if(Core::findFirst($o->core_id)){
                            if(!$core_zone->save()){
                                foreach($core_zone->getMessages() as $m){
                                    $stack_trace[] = $m->getMessage();
                                }
                            }    
                        }else{
                            return ResponseHandler::post($this, false, false, ['controller.import.inconsistent.core' => $i]);    
                        }
                    }else{
                        return ResponseHandler::post($this, false, false, ['controller.import.inconsistent.city' => $i]);    
                    }
                    
                }
                if(count($stack_trace)){
                    $transaction->rollback('something happen');
                }else{
                    $transaction->commit();
                    $link = LogPlugin::export('success', User::findFirst($this->di->get('session')->id), $start_time, 'Regiões Eleitorais', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
                    return ResponseHandler::post($this, ['quantity' => $object->quantity]);
                }
            }
        }catch (TxFailed $e){
            $link = LogPlugin::export('error', User::findFirst($this->di->get('session')->id), $start_time, 'Regiões Eleitorais', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
            return ResponseHandler::post($this, false, false, ['errors' => $stack_trace]);
        } 
    }
}