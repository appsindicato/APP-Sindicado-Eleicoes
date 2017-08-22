<?php
/**
* Model responsável pela relação entre chapa e candidato
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use Phalcon\Validation\Validator\Uniqueness as Uniqueness;
class PlaqueCandidate extends ModelBase
{
    /**
     * Handle nested results
     */
    public static $relations = [
        'Candidate' => 'candidate_id'
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
     * @Column(type='integer', length=11, nullable=false)
     */
    public $candidate_id;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $plaque_id;
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
            ['candidate_id','plaque_id','valid'],
            new Uniqueness(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.plaque_candidate.plaque_candidate_id.uniquiness'
                ]
            )
        );

        $validator->add(
            'plaque_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.plaque_candidate.plaque_id.notfound'
                ]
            )
        );
        $validator->add(
            'candidate_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.plaque_candidate.candidate_id.notfound'
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
        $this->belongsTo('candidate_id', '\Candidate', 'id', ['alias' => 'Candidate']);
        $this->belongsTo('plaque_id', '\Plaque', 'id', ['alias' => 'Plaque']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'plaque_candidate';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
    }

}
