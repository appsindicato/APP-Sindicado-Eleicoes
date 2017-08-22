<?php

use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * ReadOnlyMiddleware
 *
 * Fails when system is in readonly state
 */
class ReadOnlyMiddleware implements MiddlewareInterface
{
    public function call(Phalcon\Mvc\Micro $app)
    {
		$method = $app->request->getMethod();
		$url = $app->request->getUri();
		if ( ($method != 'GET' && $method != 'OPTIONS') &&  Config::isLocked()){
			$app->response
				->setStatusCode(405,"Method Not Allowed")
				->setContent("<style>body {background-color:#0F4166; text-align:center; color:white;}</	style><h1>405! <br> It seems that the system is locked for maintenance</h1>");
				return false;
		} else if (strpos($url,'/box/suspend') !== FALSE && $method == 'GET' && Config::electionDate()){
			$app->response
				->setStatusCode(423,"Locked")
				->setContent("<style>body {background-color:#0F4166; text-align:center; color:white;}</	style><h1>405! <br> Can't accept request</h1>");
			return false;
		}
    }
}