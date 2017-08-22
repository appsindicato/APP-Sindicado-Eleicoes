<?php

use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * CacheMiddleware
 *
 * Handles the application cache. Save/Delete the local cache
 */
class CacheMiddleware implements MiddlewareInterface
{
	private static $_unsafeMethods = array('POST','PUT','DELETE');

    public function call(Phalcon\Mvc\Micro $app)
    {

    	$method = $app->request->getMethod();
    	if ($method != 'OPTIONS'){
	    	$controllerKey = md5(get_class($app->getActiveHandler()[0]));
	    	$cacheMasterKey = $controllerKey . md5( '/' . explode('/',$app->request->getQuery()['_url'])[1] );
	    	$cacheKey = $controllerKey . md5( $app->request->getQuery()['_url'] );
	    	if (in_array($method,self::$_unsafeMethods)){
				if ( $app->caching->exists($cacheKey) ) {
					// Excludes the hit data from cache
					if ( $app->caching->delete($cacheKey) ){
						if ($app->caching->exists($cacheMasterKey)){
							// Excludes the 'master' hit from cache if exists
							return $app->caching->delete($cacheMasterKey);
						} else {
							return true;
						}
					} else {
						return false;
					}
				}
				return false;
			} else if ($method == 'GET'){
				$app->caching->save($cacheKey,json_decode($app->response->getContent()));
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
    }
}