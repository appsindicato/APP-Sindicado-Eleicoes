<?php
/**
* Controller responsável pela criptografia
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Crypto\RSA as RSA;
class ConfigureController extends \Phalcon\Mvc\Controller
{
	/**
	* @method responsável por retornar o MD5 atual
	*/
    public function install(){
        ResponseHandler::get($this,[ 'md5' => $this->config->sysconfig->md5, 'device_id' => $this->config->sysconfig->deviceid]);
    }
    /**
	* @method responsável configurar a urna
	* @param id int
	*/
    public function urna($id = null){
		try {
			if (!$id)
				throw new \InvalidArgumentException('Invalid id');
			
			$box = Box::findFirst(['id = ?1 and valid=1','bind' => [ 1 => $id ]], false);

			if (!$box)
				throw new \InvalidArgumentException('Urna não encontrada');

			if ($box->active_time)
				throw new \RuntimeException('Urna já ativada');

			/**
			 * Bloqueia sistema para modificações futuras até que a eleição seja finalizada
			 */ 
			if (!Config::isLocked()){
				$config = Config::findFirst(['locked = 0']);
				$config->locked = 1;
				if (!$config->save())
					throw new \RuntimeException("Erro ao salvar dados de configuração");
					
    		}
			
			$encrypt = new RSA();

			$keys = $encrypt->keysToFile();

			if ( !$keys )
				throw new \RuntimeException('Não foi possível gerar arquivos de chave privada e pública.');
			
			$file = new FilePlugin($this->config->amazon->key,$this->config->amazon->secret,'appsindicato-data');
			if ( !$file->upload($keys['private'],'urnas/'.$id.'/keys/priv.key') )
				throw new \RuntimeException('Não foi possível fazer upload do arquivo de chaves');
			
			$box->install();

			$this->response
                    ->setStatusCode(200, 'Accepted')
                    ->setContent($encrypt->keys['public']);

		} catch (\InvalidArgumentException $e){
			ResponseHandler::get($this,['error' => $e->getMessage()]);
		} catch (\RuntimeException $e){
			ResponseHandler::get($this,['error' => $e->getMessage()]);
		}
	}
	/**
	* @method responsável enviar o banco de dados
	* @param id int
	*/
	public function database(){
		try {
			$s3 = new FilePlugin($this->config->amazon->key,$this->config->amazon->secret,'appsindicato-artifacts');
				
			$handler = $s3->get('databases/baseUrna.sqlite');
			$file = (string) $handler['Body'];
			$this->response
                    ->setStatusCode(200, 'Accepted')
                    ->setContentType('binary/octet-stream')
                    ->setHeader("Content-Size",$handler['ContentLength'])
                    ->setHeader("Content-Disposition","attachment")
                    ->setHeader('Accept-Ranges','bytes')
                    ->setContent($file);
		} catch (\Exception $e){
			ResponseHandler::get($this,['error' => $e->getMessage()]);
		}
	}
	/**
	* @method responsável configurar o sistema
	* @param id int
	*/
	public function electionDate(){
		try {
			$request = $this->di->get('request')->getJsonRawBody();
			
			if (!$request)
				throw new \InvalidArgumentException('Requisição inválida');

			if (!isset($request->date1) || !isset($request->date2))
				throw new \InvalidArgumentException('Faltam argumentos para a requisição');

			$date1 = DateTime::createFromFormat('d/m/Y',$request->date1);
			$date2 = DateTime::createFromFormat('d/m/Y',$request->date2);
						
			$config = Config::findFirst();
			$config->start_date = $date1->getTimestamp();
			$config->end_date = $date2->getTimestamp();
			if (!$config->save()){
				throw new \RuntimeException('Erro ao salvar dados de configuração');
				ResponseHandler::get($this,['error' => $e->getMessage()]);
			}
			ResponseHandler::get($this,$config);
		} catch (\Exception $e){
			ResponseHandler::get($this,['error' => $e->getMessage()]);
		}
	}

}
