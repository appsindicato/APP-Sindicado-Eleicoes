<?php
/**
* Model responsável pelas chapas
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as Uniqueness;
class Plaque extends ModelBase
{
    /**
     * Handle nested results
     */
    public static $relations = [
        'PlaqueType' => 'plaque_type_id',
        'PlaqueCandidate' => 'id',
        'Core' => 'core_id'
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
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $plaque_type_id;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=true)
     */
    public $core_id;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=true)
     */
    public $city_id;
    /**
     *
     * @var string
     * @Column(type='string', length=50, nullable=false)
     */
    public $name;
    /**
     *
     * @var int
     * @Column(type='int', length=11, nullable=false)
     */
    public $number;
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
                    'message' => 'model.plaque.name.uniqueness'
                ]
            )
        );

        $validator->add(
            'plaque_type_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.plaque.plaque_type_id.notfound'
                ]
            )
        );

        $validator->add(
            'election_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.plaque.election_id.notfound'
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
        $this->hasMany('id', 'PlaqueCandidate', 'plaque_id', ['alias' => 'PlaqueCandidate']);
        $this->belongsTo('election_id', '\Election', 'id', ['alias' => 'Election']);
        $this->belongsTo('plaque_type_id', '\PlaqueType', 'id', ['alias' => 'PlaqueType']);
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
        return 'plaque';
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
