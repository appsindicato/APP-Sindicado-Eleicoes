<?php

use Phalcon\Http\Response\Headers;
class ResponseHandler
{

    static public function badRequest($app){
        $app->response
                    ->setStatusCode(400, 'Bad Request');
    }

    static public function forbidden($app){
        $app->response
                    ->setStatusCode(403, 'Forbidden');
    }

    static public function get($app,$data)
    {
       	if($data){
    		$app->response
                    ->setStatusCode(200, 'Accepted')
                    ->setJsonContent($data);
    	} else {
    		$app->response
                    ->setStatusCode(404, 'Not Found');
    	}
    	return true;
    }

    static public function post($app,$data,$location = null,$error = null)
    {
        if($data){
            $app->response
                    ->setStatusCode(201, 'Created')
                    ->setHeader("Location", $location )
                    ->setJsonContent($data);
        } else {
            $app->response
                    ->setStatusCode(409, 'Conflict')
                    ->setJsonContent($error);
        }
        return true;
    }

    static public function put($app,$data,$id = true,$error = null)
    {
        if ($error){
            $app->response
                    ->setStatusCode(409, 'Conflict')
                    ->setJsonContent($error);
        } else {
            if ($id) {
                if($data){
                    $app->response
                            ->setStatusCode(200, 'Accepted')
                            ->setJsonContent($data);
                } else {
                    $app->response
                            ->setStatusCode(204, 'Not Content');
                }
            } else {
                $app->response
                            ->setStatusCode(404, 'Not Found');
            }
        }
        return true;
    }

    static public function delete($app,$data)
    {
        if($data){
            $app->response
                    ->setStatusCode(204, 'No Content');
        } else {
            $app->response
                    ->setStatusCode(404, 'Not Found');
        }
        return true;
    }

    static public function file($app,$data,$mime = null)
    {
        if($data){
            if (!$mime)
                $mime = 'application/html';
            $app->response
                    ->setStatusCode(200, 'Accepted')
                    ->setContentType($mime)
                    ->setContent($data);
        } else {
            $app->response
                    ->setStatusCode(404, 'Not Found');
        }
        return true;
    }

}

