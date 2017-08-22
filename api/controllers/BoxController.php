<?php
/**
* Controller responsável pelas urnas
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
class BoxController extends RestController
{
    /*
    * @property fields allowed to restController
    */
	public static $_columns = 'id,core_id,city_id,locale,valid';
    /**
    * Allowed methods 
    */
	public static $restricted = array(
        'get' => false,
        'put' => true,
        'post' => false,
        'delete' => false
    );
    /**
    * Allow Cache
    */
	protected $cacheable = false;
    /**
     * Allows search by and partial by fields
     */
    public static $_allow = array(
        'search' => array('id','core_id','city_id','locale','valid'),
        'partials' => array('id','core_id','city_id','locale','valid')
    );
    /**
    * Remove a data de ativação para regerar as chaves da urna
    * @param $id int
    * @return bool
    */
    public function suspend ($id = null){
        if(!$id){
            throw new \RuntimeException('Arquivos não encontrados');
        }

        if($box = Box::findFirst($id, false)){
            return ResponseHandler::get($this, $box->purge());
        }
    }
    /**
    * Devolve um conjunto com informações das pessoas que votaram em duplicidade
    * @param $id int
    * @return array
    */
    public function transit ($id = null){
        if(!$id){
            throw new \RuntimeException('Arquivos não encontrados');
        }

        $vts = VoterElection::count(
            [
                'group' => 'voter_finish_code'
            ]);

        $r = [];

        foreach($vts as $vt){
            if($vt->rowcount > 1){
                $r[] = [
                    'voter' => $vt->voter_finish_code, 
                    'votes' => $vt->rowcount, 
                    'box' => VoterElection::find(
                        [
                            'voter_finish_code = ?1 ',
                            'bind' => [1 => $vt->voter_finish_code],
                            "columns" => ["box_id"]
                        ], false)];
            }
        }
        return ResponseHandler::get($this, $r);
    }
}