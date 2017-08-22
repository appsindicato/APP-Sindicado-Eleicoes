<?php 
/**
* Controller responsável pelos uploads
* @copyright GPL Version 3 © 2017 
* @author APP - Sindicato <contato@app.com.br>
* @license GPL Version 3
* 
*/
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
class UploadController extends \Phalcon\Mvc\Controller
{
	/**
    * @method Responsável pelos uploads dos arquivos de importação
    */
	public function post(){
		try {
			$request = $this->di->get('request');

			if($request->hasFiles()){
				$file = new FilePlugin($this->config->amazon->key,$this->config->amazon->secret,'appsindicato-artifacts');
				foreach ($this->request->getUploadedFiles() as $f) {
                	$aws_name = time().'.'.$f->getName();
                	if ( !$file->upload($f->getTempName(),'/import-files/'.$aws_name) )
						throw new \RuntimeException('Não foi possível fazer upload do arquivo');
					else{
						return ResponseHandler::post($this, ['name'=> $aws_name]);
					}
            	}	
			}	

		} catch (\RuntimeException $e){
			var_dump($e->getMessage());exit;
			ResponseHandler::get($this,['error' => $e->getMessage()]);
		}
	}
	/**
    * @method Responsável pelos uploads dos arquivos de voto
    */
	public function vote(){
		try {
			$file_data = [
				'votes.vt' => [
					'id' => 0,
					'lines' => 0,
					'content'=> []
				],
				'result.vt' => [
					'id' => 0,
					'lines' => 0,
					'content'=> []
				],	
			];

			$request = $this->di->get('request');
			$manager = new TxManager();
	        $transaction = $manager->get();

			/**
			* INÍCIO DAS VALIDAÇÕES
			*/

			if($request->hasFiles()){
				foreach ($this->request->getUploadedFiles() as $f) {
            		$file = fopen($f->getTempName(), 'r+');
            		$line = fgets($file);
					$b_decoded = base64_decode($line);
					$box = json_decode($b_decoded);
					$file_data[$f->getName()]['id'] = $box->urna;
					$file_data[$f->getName()]['lines'] = FilePlugin::lines($file);
            	}
			}else{
				throw new \RuntimeException('Arquivos não encontrados');
			}

			$decrypt = \Crypto\Decrypt::key($file_data['votes.vt']['id'], $this->config->amazon->key, $this->config->amazon->secret, 'appsindicato-data');

			$box = Box::findFirst($file_data['votes.vt']['id'], false);

			//verifica se os arquivos estão com o mesmo cabecalho
			if($file_data['votes.vt']['id'] !== $file_data['result.vt']['id']){
				//something goes VERY WRONG
				throw new \RuntimeException('Urna já finalizada');
				//gerar um log detalhado
			}


			//verifica se a urna não foi transmitida antes
			if($box->finish_timestamp > 0){
				//something goes VERY WRONG
				throw new \RuntimeException('Arquivos de urnas diferentes');
				//gerar um log detalhado
			}

			//verifica se as senhas estão de acordo
			if($box->password_open_1  != $request->get('password_1') || $box->password_open_2  != $request->get('password_2')){
				throw new \RuntimeException('Senhas não conferem');
				//gerar um log detalhado
			}
			
			//verifica se os arquivos estão com a mesma quantidade de linhas
			if($file_data['votes.vt']['lines'] !== $file_data['result.vt']['lines']){
				//something goes VERY WRONG
				throw new \RuntimeException('Quantidade de linhas incompatível');
				//gerar um log detalhado
			}

			foreach ($this->request->getUploadedFiles() as $f) {
        		$file = fopen($f->getTempName(), 'r+');
        		$i=0;
        		while(($l = fgets($file)) !== false){
        			if($i > 0){
						$b_decoded = base64_decode($l);
						if($f->getName() != 'result.vt'){
							$rsa_decoded = Crypto\Decrypt::rsa($b_decoded, $decrypt);		
							$content = json_decode($rsa_decoded, true);
						}else{
							$content = json_decode($b_decoded, true);
						}					
						$key = key($content);
						$file_data[$f->getName()]['content'][$key] = $content[$key];
        			}
        			$i++;
        		}
        	}

        	//compara se existe alguma divergência entre a quantidade de linhas e aquantidade de eleitores reais
        	if($file_data['result.vt']['lines'] !== (count($file_data['result.vt']['content']) + 1)){
        		//something goes VERY WRONG
        		throw new \RuntimeException('Quantidade de linhas do arquivo não confere com informações do arquivo');
        		//gerar um log detalhado
        	}

        	//compara se existe alguma divergência entre a quantidade de linhas e aquantidade de votos reais
        	if($file_data['votes.vt']['lines'] !== (count($file_data['votes.vt']['content']) + 1)){
        		//something goes VERY WRONG
        		throw new \RuntimeException('Quantidade de linhas do arquivo não confere com informações do arquivo');
        		//gerar um log detalhado
        	}

        	/**
        	* FIM DAS VALIDAÇÕES
        	*/

			$file = new \FilePlugin($this->config->amazon->key,$this->config->amazon->secret,'appsindicato-data');
			
			$aws_timestamp = time(); 
			foreach ($this->request->getUploadedFiles() as $f) {
            	$aws_name = $aws_timestamp.'.'.$f->getName();
            	if ( !$file->upload($f->getTempName(),'/urnas/'.$box->id.'/'.$aws_name) ){
					throw new \RuntimeException('Não foi possível fazer upload do arquivo de voto');
					//gerar um log detalhado
            	}
        	}        	

        	$box->finish_timestamp = $aws_timestamp;


        	//não conseguiu salvar
        	if(!$box->save()){
        		throw new \RuntimeException('Não foi possível salvar o momento de finalização da urna');
        	}

    		try{
		        $stack_trace = [];

		        $result_index = 0;

		        $election_id = Election::findFirst(['valid = 1'])->id;
		        $core_id = Core::FindFirst($box->core_id)->id;

		        foreach($file_data['result.vt']['content'] as $key => $value){
		        	$voterElection = new VoterElection();
		        	$voterElection->voter_finish_code = substr($value[0], 0, 15);
		        	$voterElection->box_id = $box->id;
		        	$voterElection->transit = $value[1];
		        	$voterElection->election_id = $election_id;
		        	error_log('processando eleitores'.$result_index);
		        	if(!$voterElection->save()){
		      			foreach($voterElection->getMessages() as $m){
                                $a = $m->getMessage();
                                error_log($vote_index . '=>'.$a);
                                $stack_trace[$result_index] = $a;
                            }
		        	}
		        	$result_index++;
		        }
		        $vote_index = 0;		        

		        $pdf_data = [];
		        $pdf[] = ["Urna", "Hora Proc.", "Tipo", "Chapas", "Votos"];

		        foreach($file_data['votes.vt']['content'] as $key => $value){
		        	foreach($value as $v){
		        		if(!is_null($v)){
		        			error_log('$v');
			        		$transit = count($value) == 4 ? 0 : 1;
			        		$pdf_data[$v] += 1;
			        		$vote = new Vote();
				        	$vote->plaque_id = $v;
				        	$vote->box_id = $box->id;
				        	$vote->timestamp = $key;
				        	$vote->core_id = $core_id;
				        	$vote->election_id = $election_id;
				        	$vote->vote_time = $key;
				        	$vote->transit = $transit;

				
				        	if(!$vote->save()){
				        		foreach($vote->getMessages() as $m){
		                            $a = $m->getMessage();
		                            error_log($vote_index . '=>'.$a);
		                            $stack_trace[$vote_index] = $a;
		                        }
				        	}
			        	}
		        	}
		        	$vote_index++;
		        }

		        $proc_date = date('d/m/Y H:i');
		        foreach($pdf_data as $key => $value){
		        	if($key > 0){
		        		$p = Plaque::findFirst($key);
		        		$pdf[] = [$box->id, $proc_date, PlaqueType::findFirst($p->plaque_type_id)->name, $p->name, $value];
		        	}
		        }

		        if(count($stack_trace)){
                    $transaction->rollback('something happen');
                }else{
                    $transaction->commit();
                    $this->response
			                    ->setStatusCode(200, 'Accepted')
			                    ->setContentType('application/pdf')
			                    ->setHeader("Content-Disposition","attachment")
			                    ->setContent(PdfPlugin::generate($pdf, [15, 50, 50, 50, 15])); 
                };
    		}catch(TxFailed $e){
    			throw $e;        			
    		}
		
		return true;

		} catch (\RuntimeException $e){
			ResponseHandler::get($this,['error' => $e->getMessage()]);
		}
	}
}