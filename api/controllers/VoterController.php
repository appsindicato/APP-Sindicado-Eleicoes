<?php
/**
* Controller responsável pelas eleitores
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
class VoterController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,election_id,status,core_id,name,document,transit,city_id,valid';
    /**
    * Allowed methods 
    */
	public static $restricted = array(
        'get' => false,
        'put' => true,
        'post' => true,
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
        'search' => array('id', 'election_id', 'status', 'core_id', 'name', 'document', 'transit','city_id','status'),
        'partials' => array('id', 'election_id', 'status', 'core_id', 'name', 'document', 'transit','city_id')
    );
    /**
    * @method Responsável por importar a base de eleitores
    */
    public function import(){
        try{
            set_time_limit (240);
            $request = $this->di->get('request')->getJsonRawBody();
            if(!$request->filename){
                return ResponseHandler::post($this, null, null, ['error'=> ['controller.voter.filename.notfound']]);
            }
            $object = ImportPlugin::import($request->filename, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');

            if($object->quantity === 0){
                return ResponseHandler::post($this, null, null, ['error' => $object->content]);
            }
            // Create a transaction manager
            $manager = new TxManager();

            // Request a transaction 
            $transaction = $manager->get();

            $stack_trace = [];
            $i = 0;
            if($object && $object->quantity > 0){
                foreach($object->content as $o){
                    $i++;
                    $voter = new Voter();
                    $voter->name = $o->name;
                    $voter->document = $o->document;
                    $voter->transit = (int)$o->transit;
                    $voter->status = (int)$o->status;
                    $voter->core_id = (int)$o->core_id;
                    $voter->city_id = (int)$o->city_id;
                        if(!$voter->save()){
                            foreach($voter->getMessages() as $m){
                                $a = $m->getMessage();
                                error_log($i . '=>'.$a);
                                $stack_trace[] = $a;
                            }
                        }
                }
                if(count($stack_trace)){
                    $transaction->rollback('something happen');
                }else{
                    $transaction->commit();
                    $link = LogPlugin::export('success', User::findFirst($this->di->get('session')->id), $start_time, 'Eleitores', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
                    return ResponseHandler::post($this, ['quantity' => $object->quantity]);
                }
            
}        }catch (TxFailed $e){
            $link = LogPlugin::export('error', User::findFirst($this->di->get('session')->id), $start_time, 'Eleitores', $stack_trace, $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-artifacts');
            return ResponseHandler::post($this, false, false, ['errors' => $stack_trace]);
        } 
    }
}