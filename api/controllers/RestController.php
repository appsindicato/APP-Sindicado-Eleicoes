<?php
/**
* Controller responsável pela abstração dos métodos nos controllers
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class RestController extends \Phalcon\Mvc\Controller
{

    protected $isSearch = false;

    protected $isPartial = false;

    protected $limit = null;

    protected $offset = null;

    protected $searchFields = null;

    protected $partialFields = null;

    protected $cacheable = false;

    protected function parseSearchParameters($unparsed){
        $unparsed = trim($unparsed, '()');
        $splitFields = explode(',', $unparsed);
        $mapped = array();
        foreach ($splitFields as $field) {
            $splitField = explode(':', $field);
            $mapped[$splitField[0]] = $splitField[1];
        }
        return $mapped;
    }

    protected function parsePartialFields($unparsed){
        return explode(',', trim($unparsed, '()'));
    }

    protected function parseRequest($controller_name){
        $request = $this->di->get('request');

        $searchParams = $request->get('q', null, null);
        $fields = $request->get('fields', null, null);

        $this->limit = ($request->get('limit', null, null)) ?: $this->limit;
        $this->offset = ($request->get('offset', null, null)) ?: $this->offset;

        if($searchParams){
            $this->isSearch = true;
            $this->searchFields = $this->parseSearchParameters($searchParams);
            if(array_diff(array_keys($this->searchFields), $controller_name::$_allow['search'])){
                return false;
            }
        }

        if($fields){
            $this->isPartial = true;
            $this->partialFields = $this->parsePartialFields($fields);
            if(array_diff($this->partialFields, $controller_name::$_allow['partials'])){
                return false;
            }
        }

        return true;

    }

    private function _getFromCache(){
        if ($this->cacheable){
            $cache = $this->caching->get(md5(get_class($this)) . md5($this->di->get('request')->getQuery()['_url']));
            if ($cache === null){
                return false;
            } else {
                return $cache;
            }
        } else {
            return false;
        }
    }

    public function count($id = null){
        if ( ! $this->parseRequest(get_class($this)) ) {
            return ResponseHandler::badRequest($this);
        }

        $model_name = str_replace('Controller','',get_class($this));
        $controller_name = $model_name.'Controller';
        if (property_exists($controller_name,'restricted')){
            if (isset($controller_name::$restricted['get'])){
                if ($this->session->get('id') != $id && $this->session->get('level') != 10){
                    return ResponseHandler::forbidden($this);
                }
            }
        }


        if ( property_exists($model_name, 'valid') ){
            $delete_condition = ' AND valid=1';
        } else {
            $delete_condition = '';
        }
        
        if ($this->isSearch){
            $fieldsArray = "1=1 ";
            $field_id = 0;
            foreach ($this->searchFields as $field => $field_value){
                $bindArray[$field_id] = $field_value;
                $fieldsArray .= "AND ".$field."=?".$field_id; 
                $field_id++;
            }
            $findArray = array(
                    $fieldsArray.$delete_condition,
                    "bind" => $bindArray
            );
        } else {
            $findArray = array(
                    "1=1 ".$delete_condition
            );
        }
        $count = $model_name::count($findArray);
        
        return ResponseHandler::get($this,['total' => $count]);
    }

    public function get($id = null){
        if ( ! $this->parseRequest(get_class($this)) ) {
            return ResponseHandler::badRequest($this);
        }

        $model_name = str_replace('Controller','',get_class($this));
        $controller_name = $model_name.'Controller';
        if (property_exists($controller_name,'restricted')){
            if (isset($controller_name::$restricted['get'])){
                if ($this->session->get('id') != $id && $this->session->get('level') != 10){
                    return ResponseHandler::forbidden($this);
                }
            }
        }



        if (!($object = $this->_getFromCache())){

            if ( property_exists($model_name, 'valid') ){
                $delete_condition = ' AND valid=1';
            } else {
                $delete_condition = '';
            }

            if ($id){
                $object = $model_name::findFirst(array( "id =?1 ".$delete_condition,
                                                        "bind" => array( 1 => $id),
                                                        "columns" => $controller_name::$_columns));
           } else {
             
                if ($this->isSearch){
                    $fieldsArray = "1=1 ";
                    $field_id = 0;
                    foreach ($this->searchFields as $field => $field_value){
                        $bindArray[$field_id] = $field_value;
                        $fieldsArray .= "AND ".$field."=?".$field_id; 
                        $field_id++;
                    }
                    $findArray = array(
                            $fieldsArray.$delete_condition,
                            "bind" => $bindArray
                    );
                } else {
                    $findArray = array(
                            "1=1 ".$delete_condition
                    );
                }

                $findArray = $this->limit ? array_merge($findArray,array("limit" => $this->limit)):$findArray;
                $findArray = $this->offset ? array_merge($findArray,array("offset" => $this->offset)):$findArray;

                if ($this->isPartial){
                    $findArray = array_merge($findArray, array("columns" => implode(',',$this->partialFields)));
                } else {
                    $findArray = array_merge($findArray, array("columns" => $controller_name::$_columns));
                }

                $object = $model_name::find($findArray,!$this->isPartial);

                if (is_object($object))
                    $object = $object->toArray();
            }
        }

        return ResponseHandler::get($this,$object);
    }

    private function _create_internal_node($model,$node){
        $object = new $model();
        if ($node){
            foreach($node as $key => $value){
                if (is_object($value)){
                    $object->$key = $this->_create_internal_node($key,$value);
                } else {
                    if (property_exists($model, $key)){
                        $object->$key = $value;
                    }
                }
            }
        }
        return $object;
    }

    private function _update_internal_node($model,$node,$object = null){
        if ($object){
            if ($node){
                foreach($node as $key => $value){
                    if (is_object($value)){
                        $object->$key = $this->_update_internal_node($key,$value,$object->$key);
                    } else {
                        if (property_exists($model, $key)){
                            $object->$key = $value;
                        }
                    }
                }
            }
        }
        return $object;
    }

    public function post(){
        $model_name = str_replace('Controller','',get_class($this));
        $controller_name = $model_name.'Controller';
        $request = $this->di->get('request')->getJsonRawBody();
        $object = $this->_create_internal_node($model_name,$request);
        $r = $object->save();
        if ( $r == false ){
            foreach($object->getMessages() as $m){
                $r['error'][] = (string) $m;
            }
            return ResponseHandler::post($this,false,false,$r);
        } else {
            $created_object = $model_name::findFirst(array( "id =?1",
                                                    "bind" => array( 1 => $object->id),
                                                    "columns" => $controller_name::$_columns));
            $r = $created_object;
            return ResponseHandler::post($this,$r);
        }
    }

    public function put($id = null){
        $model_name = str_replace('Controller','',get_class($this));
        $controller_name = $model_name.'Controller';
        if (property_exists($controller_name,'restricted')){
            if (isset($controller_name::$restricted['put'])){
                if ($this->session->get('id') != $id && $this->session->get('level') != 10){
                    return ResponseHandler::forbidden($this);
                }
            }
        }
        $object = $this->_update_internal_node($model_name,$this->di->get('request')->getJsonRawBody(),$model_name::findFirst($id,false));
        if ($object){
            if ($object->save()){
                $updated_object = $model_name::findFirst(array( "id =?1",
                                                    "bind" => array( 1 => $object->id),
                                                    "columns" => $controller_name::$_columns));
                return ResponseHandler::put($this,$updated_object,$object->id);
            } else {
                foreach($object->getMessages() as $m){
                    $err['error'][] = (string) $m;
                }
                return ResponseHandler::put($this,false,$object->id,$err);
            }
        } else {
            return ResponseHandler::put($this,false,false);
        }
    }

    public function delete($id = null){
        $model_name = str_replace('Controller','',get_class($this));
        $controller_name = $model_name.'Controller';
        if (property_exists($controller_name,'restricted')){
            if (isset($controller_name::$restricted['delete'])){
                if ($this->session->get('level') != 10){
                    return ResponseHandler::forbidden($this);
                }
            }
        }

        if ($id){
            if ($object = $model_name::findFirst($id,false)){
                return ResponseHandler::delete($this,$object->delete());
            }
        } else {
            $object = null;
        }
        return ResponseHandler::delete($this,$object);
    }


}

