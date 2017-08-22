<?php
/**
* Model responsável pelos candidatos
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use \Phalcon\Mvc\Model\Message as Message;
class Candidate extends ModelBase
{
    /**
    * Handle nested results
    */
    public static $relations = [
        'candidateOffice' => 'candidate_office_id'
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
    * @Column(type='string', length=50, nullable=false)
    */
    public $first_name;
    /**
    *
    * @var string
    * @Column(type='string', length=200, nullable=false)
    */
    public $last_name;
    /**
    *
    * @var string
    * @Column(type='string', length=255, nullable=false)
    */
    public $email;
    /**
    *
    * @var string
    * @Column(type='string', length=15, nullable=false)
    */
    public $document;
    /**
    *
    * @var integer
    * @Column(type='integer', length=4, nullable=false)
    */
    public $valid;
    /**
    *
    * @var integer
    * @Column(type='integer', length=11, nullable=false)
    */
    public $candidate_office_id;
    /**
    * Validations and business logic
    *
    * @return boolean
    */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.candidate.email.notfound'
                ]
            )
        );

        $validator->add(
            'document',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.candidate.document.notfound'
                ]
            )
        );

        $validator->add(
            'first_name',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.candidate.first_name.notfound'
                ]
            )
        );

         $validator->add(
            'last_name',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.candidate.last_name.notfound'
                ]
            )
        );

        $validator->add(
            'candidate_office_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.candidate.candidate_office_id.notfound'
                ]
            )
        );

        $validator->add(
            'election_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.candidate.election_id.notfound'
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
        $this->hasMany('id', 'PlaqueCandidate', 'candidate_id', ['alias' => 'PlaqueCandidate']);
        $this->belongsTo('candidate_office_id', '\CandidateOffice', 'id', ['alias' => 'CandidateOffice']);
        $this->belongsTo('election_id', '\Election', 'id', ['alias' => 'Election']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'candidate';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
        $this->first_name = ucwords(strtolower($this->first_name));
        $this->last_name =  ucwords(strtolower($this->last_name));
        $this->election_id = Election::findFirst(['valid = 1'])->id;
    }

}
