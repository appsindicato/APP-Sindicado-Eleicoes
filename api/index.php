<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\DI\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro\Collection as MicroCollection;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Config\Adapter\Ini as IniConfig;
use Phalcon\Config\Adapter\Json AS JsonConfig;

try {
    /**
     * Autoloader
     */
    $loader = new Loader();
    $config = new IniConfig("config/config.ini");

    $loader->registerDirs(array(
        __DIR__ . $config->api->pluginsDir,
        __DIR__ . $config->api->modelsDir,
        __DIR__ . $config->api->controllersDir,
        __DIR__ . $config->api->constantsDir,
        __DIR__ . $config->api->middlewareDir
    ));
    /*Load incubator librarys*/
    $loader->registerNamespaces(array(
        'Crypto' => __DIR__ . $config->api->libraryDir.'Crypto/'
    ));
    $loader->register();
    /**
     * Include Services
     */
    $di = new FactoryDefault();
    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    $di->setShared('db', function () use ($config) {
        $dbConfig = $config->database->toArray();
        $dbConfig['options'] = array(
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        );
        $adapter = $dbConfig['adapter'];
        unset($dbConfig['adapter']);
        $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;
        return new $class($dbConfig);
    });


    $di->setShared('session', function () {
         $session = new Session();
         $session->start();
         return $session;
    });

    $di->set('config',$config);

    $di->set('caching', function() use ($config){
        $frontCache = new \Phalcon\Cache\Frontend\Data(array(
            'lifetime' => $config->cache->lifetime
        ));

        $cache = new \Phalcon\Cache\Backend\File($frontCache, array(
            'cacheDir' => __DIR__ . $config->cache->dir
        ));

        return $cache;
    });


    /**
     * Starting the application
     * Assign service locator to the application
     */
    $app = new MicroApi($di);
    $app->generateRoutes(new JsonConfig("config/routes.json"));
    $app->session->destroy();

    /**
     * Default not found route
     */
    $app->notFound(function () use ($app){
        $notFound = new NotFoundMiddleware();
        return $notFound->call($app);
    });

    /**
     * Options for CORS
     */ 
    $app->options('/{catch:(.*)}', function() use ($app) {
        $app->response->setStatusCode(200, "OK");
        return false;
    });

    /**
     * Set all CORS Headers
     */
    $app->before(new CORSMiddleware($app));
    
    /**
     * If the authorization fails, will stop the execution
     */
    $app->before(function () use ($app){
        $Auth = new AuthorizationMiddleware();
        return $Auth->call($app);
    });

    /**
     * Check if the system is readonly
     */
    $app->before(new ReadOnlyMiddleware($app));

    /**
     * Log the request and response
     */
    $app->after(new LogMiddleware($app));

    /**
     * Caches all successful requests
     */
    $app->after(new CacheMiddleware($app));

    /**
     * Finally send back the response if everything goes right
     */
    $app->after(function() use ($app) {  
        $app->response->send();
    });

    /**
     * Handle the request
     */

    $app->handle();


} catch (\Exception $e) {
    error_log('Exception: '.get_class($e).' ' . $e->getMessage() . ' Stack Trace: ' .$e->getTraceAsString()."\n\n",3,'./api_error.log');
    ResponseHandler::badRequest($app);
    return $app->response->send();
}
