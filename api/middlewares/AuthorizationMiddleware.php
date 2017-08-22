<?php

use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * AuthorizationMiddleware
 *
 * Handles REST Authorizations
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    public function call(Phalcon\Mvc\Micro $app)
    {
		$AclPlugin = new AclPlugin();
        if ($app->request->getServer('PHP_AUTH_USER') && $app->request->getServer('PHP_AUTH_PW')){
            if ( Auth::validate($app->request->getServer('PHP_AUTH_USER'), $app->request->getServer('PHP_AUTH_PW'),$app) ) { 
                if ( $AclPlugin->getPermission($app) ){
                    return true;
                }
                $app->response
                    ->setStatusCode(403, 'Forbidden');
            } else {
                $app->response
                    ->setStatusCode(401, 'Unauthorized');
            }
        } else {
            if ( $AclPlugin->getPermission($app) ){
                return true;
            }
            $app->response
                    ->setStatusCode(403, 'Forbidden');
        }
        $app->response->send();
        return false;
    }
}