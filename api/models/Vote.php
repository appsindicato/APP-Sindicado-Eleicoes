<?php

/**
* Model responsável pelos votos
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use \Phalcon\Mvc\Model\Message as Message;
use Phalcon\Validation;
class Vote extends ModelBase
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
    public $plaque_id;
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
    public $core_id;
    /**
     *
     * @var integer
     * @Column(type='integer', length=11, nullable=false)
     */
    public $election_id;
    /**
     *
     * @var integer
     * @Column(type='integer', length=20, nullable=false)
     */
    public $vote_time;
    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();
        
        $validator->add(
            'plaque_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.vote.plaque_id.notfound'
                ]
            )
        );

        $validator->add(
            'box_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.vote.box_id.notfound'
                ]
            )
        );

        $validator->add(
            'transit',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.vote.transit.notfound'
                ]
            )
        );

        $validator->add(
            'core_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.vote.core_id.notfound'
                ]
            )
        );

        $validator->add(
            'election_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.vote.election_id.notfound'
                ]
            )
        );

        $validator->add(
            'vote_time',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.vote.vote_time.notfound'
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
        return 'vote';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){        
    }
}
