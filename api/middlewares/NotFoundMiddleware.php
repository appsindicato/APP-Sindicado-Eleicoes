<?php

use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * NotFoundMiddleware
 *
 * Returns not found info
 */
class NotFoundMiddleware implements MiddlewareInterface
{
    public function call(Phalcon\Mvc\Micro $app)
    {
		$origin = $app->request->getHeader('ORIGIN') ? $app->request->getHeader('ORIGIN') : '*';
		$app->response
			->setHeader("Access-Control-Allow-Origin",$origin)
			->setHeader("Access-Control-Allow-Methods",$app->config->rest->allowedMethods)
			->setHeader("Access-Control-Allow-Headers",$app->config->rest->defaultHeaders)
			->setHeader("Access-Control-Allow-Credentials", true)
			->setHeader("API-Version", $app->config->rest->version);

		$app->response
			->setStatusCode(404,"Not Found")
			->setContent("<style>body {background-color:#0F4166; text-align:center; color:white;}</style><h1>404! <br> You've broke something</h1>
		    	<img border='0' src='https://mir-s3-cdn-cf.behance.net/project_modules/disp/0efaf032676677.568ed0d61d000.gif'>");
		return $app->response;
    }
}