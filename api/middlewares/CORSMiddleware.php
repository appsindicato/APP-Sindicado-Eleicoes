<?php

use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * CORSMiddleware
 *
 * Returns the CORS Headers
 */
class CORSMiddleware implements MiddlewareInterface
{
    public function call(Phalcon\Mvc\Micro $app)
    {
    	if ($app->request->getMethod() == 'OPTIONS'){
		    $app->stop();
		}
		$origin = $app->request->getHeader('ORIGIN') ? $app->request->getHeader('ORIGIN') : '*';
		$app->response
		    ->setHeader("Access-Control-Allow-Origin",$origin)
		    ->setHeader("Access-Control-Allow-Methods",$app->config->rest->allowedMethods)
		    ->setHeader("Access-Control-Allow-Headers",$app->config->rest->defaultHeaders)
		    ->setHeader("Access-Control-Allow-Credentials", true)
		    ->setHeader("API-Version", $app->config->rest->version);
    }
}