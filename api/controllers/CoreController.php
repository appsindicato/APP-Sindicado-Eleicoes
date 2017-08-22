<?php
/**
* Controller responsável pelos núcleos
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
class CoreController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,name,city_id,election_id,code,valid';
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
        'search' => array('id', 'name', 'city_id', 'election_id', 'core'),
        'partials' => array('id', 'name', 'city_id', 'election_id', 'core')
    );
    /**
    * @method Responsável por importar os núcleos
    */
    public function import(){
        try{
            $start_time = date('d/m/Y - H:i:s');
            $request = $this->di->get('request')->getJsonRawBody();
            
            if(!$request->filename){
                return ResponseHandler::post($this, null, null, ['error'=> ['controller.core.filename.notfound']]);
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
                    $core = new Core();
                    $core->id = (int)$o->id;
                    $core->name = $o->name;
                    $core->code = (int)$o->id;
                    $core->city_id = (int)$o->city_id;
                    $core->valid = 1;
                    if(!$core->save()){
                        foreach($core->getMessages() as $m){
                            $stack_trace[] = ['line' => $i , 'message' => $m->getMessage()];
                        }
                    }
                }
                if(count($stack_trace)){
                    $transaction->rollback('something happen');
                }else{
                    $transaction->commit();
                   $link = LogPlugin::export('success', User::findFirst($this->di->get('session')->id), $start_time, 'Núcleo', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
                    return ResponseHandler::post($this, ['quantity' => $object->quantity]);
                }
            }else{
                return ResponseHandler::post($this, false, false, ['errors' => 'controller.import.file.empty']);
            }
        }catch (TxFailed $e){
            $link = LogPlugin::export('error', User::findFirst($this->di->get('session')->id), $start_time, 'Núcleo', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
            return ResponseHandler::post($this, false, false, ['link' => $link]);
        } 
    }
}