<?php

use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * LogMiddleware
 *
 * Log REST requests
 */
class LogMiddleware implements MiddlewareInterface
{
    public function call(Phalcon\Mvc\Micro $app)
    {
		if ($app->request->getMethod() != 'OPTIONS'){
            // Metodo para LOG
            $log = new Log();
            $log->user_id = $app->session->get('id');
            $log->url = $app->router->getRewriteUri();
            $log->parameters = json_encode($app->di->get('request')->getJsonRawBody());
            $log->response_code = $app->response->getStatusCode();
            $log->method = $app->request->getMethod();
            if (isset(apache_request_headers()["X-Forward-For"])){
                $log->client_ip = apache_request_headers()["X-Forward-For"];
            } else {
                $log->client_ip = $app->request->getClientAddress();
            }
            $log->log_time = time();
            $log->save();
        }
    }
}