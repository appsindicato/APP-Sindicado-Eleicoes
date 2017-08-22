<?php

require __DIR__ . '/amazon/vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class FilePlugin
{

	private $_bucket = '';
	private $_key;
	private $_secret;
	private $_client;

	public function __construct($key, $secret,$bucket = ''){
		$this->_key = $key;
		$this->_secret = $secret;
		if (!$bucket)
			throw new \InvalidArgumentException('Falta argumento obrigatório: Bucket');
		$this->_bucket = $bucket;
		$this->_client = S3Client::factory(array(
			'credentials' => array(
				'key' => $this->_key,
				'secret' => $this->_secret
			)
		));
	}

	public function upload(string $source = '',string  $destination = '',$sync = true,$private = true){
		try {
			if (!$source)
				throw new \InvalidArgumentException('Falta argumento de origem');
			if (!$destination)
				throw new \InvalidArgumentException('Falta argumento de destino');

			if (!$private)
				$acl = 'public-read';
			else
				$acl = 'private';

			$result = $this->_client->putObject(array(
				'Bucket' => $this->_bucket,
				'Key' => $destination,
				'SourceFile' => $source,
				'ACL' => $acl
			));

			if ($sync){
				$this->_client->waitUntil('ObjectExists',array(
					'Bucket' => $this->_bucket,
					'Key' => $destination
				));
			}
			$result['destination'] = $destination;
			return $result;
		} catch (S3Exception $e){
			return false;
		} catch (\InvalidArgumentException $e){
			throw $e;
		}
	}

	public function list($prefix = ''){
		try{
			$result = $this->_client->listObjects([
				'Bucket' => $this->_bucket,
				'Prefix' => $prefix
			]);
			return $result;
		} catch(\S3Exception $e){
			return false;
		}
	}

	public function get($object = null){
		if (!$object)
			return false;
		try{
			$result = $this->_client->getObject(array(
				'Bucket' => $this->_bucket,
				'Key' => $object
			));
			return $result;
		}catch(S3Exception $e){
			return false;
		}
	}

	/**
     * return quantity of lines
     * @param $file Handle from fOpen()
     * @return int $linecount 
     */
	public static function lines($file = null){
		$linecount = 0;
		if($file){
			$linecount = 1;
			while(!feof($file)){
	  			$line = fgets($file);
				if(strlen($line))
	  				$linecount++;
	  		}
		}
  		return $linecount;
	}

}

