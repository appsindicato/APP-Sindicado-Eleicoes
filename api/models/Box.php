<?php
/**
* Model responsável pelas urnas
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Behavior\SoftDelete;
use Phalcon\Validation\Validator\PresenceOf as PresenceOf;
use \Phalcon\Mvc\Model\Message as Message;
use Phalcon\Validation;
class Box extends ModelBase
{
    /**
    * Handle nested results
    */
    public static $relations = [
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
    * @Column(type='integer', length=11, nullable=false)
    */
    public $core_id;
    /**
    *
    * @var string
    * @Column(type='string', length=255, nullable=true)
    */
    public $locale;
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
    public $city_id;
    /**
    *
    * @var integer
    * @Column(type='integer', length=20, nullable=false)
    */
    public $finish_timestamp;
    /**
    *
    * @var integer
    * @Column(type='integer', length=20, nullable=false)
    */
    public $active_time;
    /**
    *
    * @var string
    * @Column(type='string', length=15, nullable=true)
    */
    public $password_operation;
    /**
    *
    * @var string
    * @Column(type='string', length=15, nullable=true)
    */
    public $password_open_1;
    /**
    *
    * @var string
    * @Column(type='string', length=15, nullable=true)
    */
    public $password_open_2;
    /**
    * Validations and business logic
    *
    * @return boolean
    */
    public function validation()
    {
        $validator = new Validation();
        $validator->add(
            'core_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.box.core_id.notfound'
                ]
            )
        );

        $validator->add(
            'locale',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.box.locale.notfound'
                ]
            )
        );

         $validator->add(
            'city_id',
            new PresenceOf(
                [
                    'model' => $this,
                    'required' => true,
                    'message' => 'model.box.city_id.notfound'
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
        $this->belongsTo('core_id', '\Core', 'id', ['alias' => 'Core']);
        $this->belongsTo('city_id', '\City', 'id', ['alias' => 'City']);
    }
    /**
    * Returns table name mapped in the model.
    *
    * @return string
    */
    public function getSource()
    {
        return 'box';
    }
    /**
     * Allows to change values before validation
     *
     */
    public function beforeValidationOnCreate(){
        $this->valid = 1;
        $this->password_operation = Box::Passwords(4);
        $this->password_open_1 = Box::Passwords(8);
        $this->password_open_2 = Box::Passwords(8);
    }
    /**
    * Create a code to authenticate
    * @param $limit int Limite de chars para a senhas
    * @return $token string 
    */
    private static function Passwords($limit = 6){
        $range = str_split('qwertyuioplkajhgfdsazxcvbnm0123456789QWERTYUIOPASDFGHJKLZXCVBNM');
        $t = count($range);
        $token = [];
        for($i=0;$i<$limit;$i++){
            $token[] = $range[rand(0,$t)];
        }
        return implode($token);
    }
    /**
    * Inicia a urna
    */
    public function install(){
        $this->active_time = time();
        $this->save();
    }

    /**
    * Limpa o active_time
    * @return mixed
    */
    public function purge(){
        $this->active_time = null;
        if($this->save()){
            return $this;
        }else{
            return false;    
        }
    }

}
