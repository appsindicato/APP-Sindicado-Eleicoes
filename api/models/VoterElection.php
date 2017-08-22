<?php
/**
* Model responsável pela apuração
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use \Phalcon\Mvc\Model\Message as Message;
use Phalcon\Validation;
class VoterElection extends ModelBase
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
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $box_id;
    /**
     *
     * @var string
     * @Column(type='string', length=15, nullable=false)
     */
    public $voter_finish_code;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $transit;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $election_id;
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
                    'message' => 'model.voter_election.election_id.notfound'
                ]
            )
        );

        $validator->add(
            'voter_finish_code',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter_election.voter_finish_code.notfound'
                ]
            )
        );

        $validator->add(
            'transit',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter_election.transit.notfound'
                ]
            )
        );

        $validator->add(
            'box_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter_election.box_id.notfound'
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
        $this->setup(
            array('notNullValidations'=>false)
        );
        $this->setSchema('app_sindicato');
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'voter_election';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){        
    }
}
