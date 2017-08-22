<?php
/**
* Model responsável pelas cidades
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
class City extends ModelBase
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
    public $id;
    /**
     *
     * @var string
     * @Column(type='string', length=255, nullable=false)
     */
    public $name;
    /**
     *
     * @var string
     * @Column(type='string', length=50, nullable=false)
     */
    public $state;
    /**
     *
     * @var integer
     * @Column(type='integer', length=4, nullable=false)
     */
    public $valid;
    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.city.id.notfound'
                ]
            )
        );

        $validator->add(
            'name',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.city.name.notfound'
                ]
            )
        );

        $validator->add(
            'state',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.city.state.notfound'
                ]
            )
        );

        $validator->add(
            'valid',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.city.valid.notfound'
                ]
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
        $this->hasMany('id', 'Core', 'city_id', ['alias' => 'Core']);
        $this->hasMany('id', 'Box', 'city_id', ['alias' => 'Box']);
        $this->hasMany('id', 'CoreZone', 'city_id', ['alias' => 'CoreZone']);
        $this->hasMany('id', 'School', 'city_id', ['alias' => 'School']);
        $this->hasMany('id', 'Voter', 'city_id', ['alias' => 'Voter']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'city';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
    }

}
