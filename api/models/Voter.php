<?php
/**
* Model responsável pelos eleitores
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
class Voter extends ModelBase
{
    /**
     * Handle nested results
     */
    public static $relations = [
        'city' => 'city_id',
        'core' => 'core_id'
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
    public $election_id;
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
    public $city_id;
    /**
     *
     * @var string
     * @Column(type='string', length=255, nullable=false)
     */
    public $name;
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
    public $transit;
    /**
     *
     * @var integer
     * @Column(type='integer', length=4, nullable=false)
     */
    public $status;
    /**
     *
     * @var integer
     * @Column(type='integer', length=4, nullable=false)
     */
    public $has_voted;
    /**
     *
     * @var integer
     * @Column(type='integer', length=4, nullable=false)
     */
    public $valid;
     /**
     *
     * @var string
     * @Column(type='string', length=10, nullable=false)
     */
    public $finish_code;
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
                    'message' => 'model.voter.election_id.notfound'
                ]
            )
        );

        $validator->add(
            'status',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter.status.notfound'
                ]
            )
        );

        $validator->add(
            'core_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter.core_id.notfound'
                ]
            )
        );

        $validator->add(
            'name',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter.name.notfound'
                ]
            )
        );

        $validator->add(
            'document',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter.document.notfound'
                ]
            )
        );

        $validator->add(
            'transit',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter.transit.notfound'
                ]
            )
        );

        $validator->add(
            'city_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.voter.city_id.notfound'
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
        $this->setSchema('app_sindicato');
        $this->belongsTo('city_id', '\City', 'id', ['alias' => 'City']);
        $this->belongsTo('core_id', '\Core', 'id', ['alias' => 'Core']);
        $this->belongsTo('election_id', '\Election', 'id', ['alias' => 'Election']);
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'voter';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
        $this->has_voted = 0;
        $this->election_id = Election::findFirst(['valid = 1'])->id;
        $this->finish_code = $this->finishCode(time());
    }
    /**
    *   Create a code to authenticate vote after election
    */
    private function finishCode(String $data){
        return strtoupper(hash('crc32',$data));
    }

}
