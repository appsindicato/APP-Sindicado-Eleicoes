<?php
/**
* Abstração dos models na API
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use \Phalcon\Mvc\Model\Message as Message;
class ModelBase extends \Phalcon\Mvc\Model
{
	/**
     * Handle nested results
     */
    public static $relations = [];
    /**
     * Clear cache for related models
     */
    public static $clearCache = [];
    /**
    * Fields not allowed
    */
    public static $forbiddenFields = [];
    /**
	 * Level-up early implementation of a cache. 
	 * Everytime a related model is updated we need to clean the cache so we dont get wrong infos.
     */
    private function cleanUpCache($cache){
    	foreach (static::$clearCache as $relation ){
    		if ( $cacheKeys = $cache->queryKeys(md5($relation.'Controller')) ){
    			foreach ($cacheKeys as $cleanUpKey){
    				$cache->delete($cleanUpKey);
    			}
    		}
    	}
    }
    /**
     * After every record update we clean the cache. Do NOT override this function without calling it back
     */
    public function afterSave()
    {
    	$this->cleanUpCache($this->getDI()->get('caching'));
    }
    /**
    * Default findFirst to models 
    */
    public static function findFirst($parameters = null,$iterate = true)
    {
        $value = parent::findFirst($parameters);
        if (!$iterate)
            return $value;

        if ($value){
            $r = $value->toArray();
            foreach (static::$relations as $relation => $key){
                $controller_name = $relation.'Controller';
                $r[$relation] = $relation::findFirst(array(
                        'id = ?1',
                        'bind' => array(1 => $value->$key),
                        'columns' => $controller_name::$_columns));
            }
            return (object) $r;
        } else {
            return null;
        }
    }
    /**
    * Default find to models 
    */
	public static function find($parameters = null,$iterate = true)
    {
        $array = parent::find($parameters);
        if (!$iterate)
            return $array;
        if ($array){
            $i = 0;
            foreach($array as $value){
                $r[$i] = $value->toArray();
                foreach (static::$relations as $relation => $key){
                  $controller_name = $relation.'Controller';
                  $r[$i][$relation] = $relation::findFirst(array(
                              'id = ?1',
                              'bind' => array(1 => $value->$key),
                              'columns' => $controller_name::$_columns));
                }
                $i++;
            }
            return $r;
        } else {
            return null;
        }
    }
    /**
    * Default validateRequests to models 
    */
    public function validateRequest($request,$translate){
        foreach ( static::$forbiddenFields as $field ){
            if ( isset($request->$field) ){
                $this->appendMessage(new Message($translate->t('error.invalidrequest')));
                return false;
            }
        }
        return true;
    }
}
