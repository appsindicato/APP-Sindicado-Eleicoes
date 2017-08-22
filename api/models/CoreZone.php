<?php
/**
* Model responsável pela relação entre núcleos e cidades
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
class CoreZone extends ModelBase
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
     * @Primary
     * @Column(type='integer', length=11, nullable=false)
     */
    public $city_id;
    /**
     *
     * @var integer
     * @Primary
     * @Column(type='integer', length=11, nullable=false)
     */
    public $core_id;
    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation(){

        $validator = new Validation();

        $validator->add(
            ['city_id', 'core_id' ],
            new PresenceOf(
                array(
                    'required' => true,
                    'message' => [
                        'core_id' =>    'model.core_zone.core_id.notfound',
                        'city_id' =>    'model.core_zone.city_id.notfound'
                    ]
                )
            )
        );

        return $this->validate($validator);
    }
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->addBehavior(
            new SoftDelete(
                array(
                    'field' => 'valid',
                    'value' => ModelConstants::DELETED
                )
            )
        );

        $this->setup(
            array('notNullValidations'=>false)
        );

        $this->setSchema('app_sindicato');
        $this->belongsTo('city_id', '\City', 'id', ['alias' => 'City']);
        $this->belongsTo('core_id', '\Core', 'id', ['alias' => 'Core']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'core_zone';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
    }

}
