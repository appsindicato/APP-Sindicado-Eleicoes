<?php
/**
* Model responsável pelas configuração
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class Config extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Column(type='integer', length=1, nullable=false)
     */
    public $locked;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $start_date;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $end_date;   
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
        return 'config';
    }
    /**
     * verifica se o sistema está trancado
     * @return bool
     */
    public static function isLocked(){
        $config = Config::findFirst();
        return $config->locked;
    }
    /**
     * o período da eleição
     * @return bool
     */
    public static function electionDate(){
        $config = Config::findFirst();
        if ($config->start_date > 0 && $config->end_date > 0 ){
            if (time() > $config->start_date && time() < $config->end_date){
                return true;
            }
        }
        return false;
    }

}
