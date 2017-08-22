<?php
/**
* Model responsável pela Eleição
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use \Phalcon\Mvc\Model\Message as Message;
use Phalcon\Validation;
class Election extends ModelBase
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
     * @Column(type='string', nullable=false)
     */
    public $start;
    /**
     *
     * @var string
     * @Column(type='string', nullable=true)
     */
    public $finish;
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
    public function validation(){

        $validator = new Validation();

        $validator->add(
            'start',
            new Presenceof(
                array(
                    'required' => true,
                    'message' => 'model.election.start.notfound'
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
        $this->hasMany('id', 'Candidate', 'election_id', ['alias' => 'Candidate']);
        $this->hasMany('id', 'Core', 'election_id', ['alias' => 'Core']);
        $this->hasMany('id', 'Plaque', 'election_id', ['alias' => 'Plaque']);
        $this->hasMany('id', 'Voter', 'election_id', ['alias' => 'Voter']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'election';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
        $this->start = date('Y-m-d H:i:s');
    }
    /**
     * Allows to change values before delete
     *
     */
    public function beforeDelete(){
        $this->finish = date('Y-m-d H:i:s');
    }
}
