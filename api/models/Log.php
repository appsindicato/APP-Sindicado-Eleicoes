<?php
/**
* Model responsável pelos logs
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class Log extends ModelBase
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
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=true)
     */
    public $user_id;
    /**
     *
     * @var string
     * @Column(type='string', length=500, nullable=true)
     */
    public $url;
    /**
     *
     * @var string
     * @Column(type='string', nullable=true)
     */
    public $parameters;
    /**
     *
     * @var string
     * @Column(type='string', length=10, nullable=true)
     */
    public $method;
    /**
     *
     * @var string
     * @Column(type='string', length=25, nullable=true)
     */
    public $client_ip;
    /**
     *
     * @var string
     * @Column(type='string', nullable=false)
     */
    public $log_time;
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema('app_sindicato');
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'log';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Log[]|Log|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }
    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Log|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
