<?php
/**
* Model responsável pelos núcleos
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as Uniqueness;
class Core extends ModelBase
{
    /**
     * Handle nested results
     */
    public static $relations = [
        'City' => 'city_id'
    ];
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
     * @var integer
     * @Primary
     * @Column(type='integer', length=11, nullable=false)
     */
    public $election_id;
    /**
     *
     * @var string
     * @Column(type='string', length=255, nullable=false)
     */
    public $name;
    /**
     *
     * @var integer
     * @Column(type='integer', length=4, nullable=false)
     */
    public $valid;
    /**
     *
     * @var string
     * @Column(type='string', length=25, nullable=false)
     */
    public $code;
    /**
     *
     * @var integer
     * @Primary
     * @Column(type='integer', length=11, nullable=false)
     */
    public $city_id;
    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'election_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.core.election_id.notfound'
                ]
            )
        );

        $validator->add(
            'name',
            new Uniqueness(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.core.name.uniqueness'
                ]
            )
        );

        $validator->add(
            'code',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.core.code.uniqueness'
                ]
            )
        );

         $validator->add(
            'city_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.core.city_id.notfound'
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
        $this->hasMany('id', 'Box', 'core_id', ['alias' => 'Box']);
        $this->hasMany('id', 'CoreZone', 'core_id', ['alias' => 'CoreZone']);
        $this->hasMany('id', 'Voter', 'core_id', ['alias' => 'Voter']);
        $this->belongsTo('city_id', '\City', 'id', ['alias' => 'City']);
        $this->belongsTo('election_id', '\Election', 'id', ['alias' => 'Election']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'core';
    }
   /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
        $this->election_id = Election::findFirst(['valid = 1'])->id;
    }

}
