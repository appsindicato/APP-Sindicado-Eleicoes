<?php
/**
* Model responsável pelas tipo de chapa
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as Uniqueness;
class PlaqueType extends ModelBase
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
     * @Identity
     * @Column(type='integer', length=11, nullable=false)
     */
    public $id;
    /**
     *
     * @var string
     * @Column(type='string', length=50, nullable=false)
     */
    public $name;
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
            'name',
            new Uniqueness(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.plaque_type.name.uniqueness'
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
        $this->hasMany('id', 'Plaque', 'plaque_type_id', ['alias' => 'Plaque']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'plaque_type';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
    }

}
