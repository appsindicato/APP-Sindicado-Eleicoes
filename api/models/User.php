<?php
/**
* Model responsável pelos usuários
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\Email as Email;
use Phalcon\Validation\Validator\Uniqueness as Uniqueness;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use Phalcon\Validation\Validator\Regex as RegexValidator;
use \Phalcon\Mvc\Model\Message as Message;
use Phalcon\Validation;
class User extends ModelBase
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
    public static $forbiddenFields = [
        'password'
    ];
    /**
    * to controll when change information
    */
    public $skipRequest = false;
    /**
     *
     * @var int
     * @Column(type='integer', lenght=11, nullable=true)
     */
    public $id;
    /**
     *
     * @var string
     * @Column(type='string', nullable=true)
     */
    public $first_name;
    /**
     *
     * @var string
     * @Column(type='string', nullable=true)
     */
    public $last_name;
    /**
     *
     * @var string
     * @Column(type='string', nullable=true)
     */
    public $email;
    /**
     *
     * @var string
     * @Column(type='string', nullable=true)
     */
    public $password;
    /**
     *
     * @var int
     * @Column(type='integer', length=11, nullable=true)
     */
    public $role;
    /**
     *
     * @var string
     * @Column(type='string', nullable=true)
     */
    public $document;
    /**
     *
     * @var int
     * @Column(type='integer', length=1, nullable=true)
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
            'email',
            new Email(
                array(
                    'required' => true,
                    'message' => 'model.user.email.notfound'
                )
            )
        );

        $validator->add(
            'first_name',
            new PresenceOf(
                array(
                    'message' => 'model.user.first_name.notfound'
                )
            )
        );

         $validator->add(
            'password',
            new PresenceOf(
                array(
                    'message' => 'model.user.password.notfound'
                )
            )
        );

        $validator->add(
            'last_name',
            new PresenceOf(
                array(
                    'message' => 'model.user.last_name.notfound'
                )
            )
        );

        $validator->add(
            'role',
            new PresenceOf(
                array(
                    'message' => 'model.user.role.notfound'
                )
            )
        );
        
        $validator->add(
            'email',
            new Uniqueness(
                array(
                    'message' => 'model.user.email.uniqueness'
                )
            )
        );

        $validator->add(
            'document',
            new PresenceOf(
                array(
                    'required' => true,
                    'message' => 'model.user.document.notfound'
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
        $this->setup(
            array('notNullValidations'=>false)
        );

        $this->addBehavior(
            new SoftDelete(
                array(
                    'field' => 'valid',
                    'value' => ModelConstants::DELETED
                )
            )
        );
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
        $this->password = sha1($this->password);
        $this->first_name = ucwords(strtolower($this->first_name));
        $this->last_name =  ucwords(strtolower($this->last_name));
    }
    
}