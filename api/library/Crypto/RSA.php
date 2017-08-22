<?php

namespace Crypto;

class RSA {

	private $config;

	private $resource;

	public $keys = array();

	public function __construct(string $alg = 'sha512',int $bits = 4096){
		$this->config = array(
				'digest_alg' => $alg,
				'private_key_bits' => $bits
		);
	}

	/**
	 * Gera uma chave RSA publica
	 * @return string Chave pública gerada
	 */
	public function publicKey(){
		$this->resource = openssl_pkey_new($this->config);
		$detail = openssl_pkey_get_details($this->resource);
		return $detail['key'];
	}

	/**
	 * Gera uma chave RSA privada
	 * @return string Chave privada gerada
	 */
	public function privateKey(){
		if (!$this->resource)
			return false;
		if ( openssl_pkey_export($this->resource, $privateKey) ){
			return $privateKey;
		} else {
			return false;
		}
	}


	public function keysToFile($sufix = '',$dir = '/tmp/'){
		$files = array();
		if ($sufix){
			$files['public'] = $dir.'pub_'.$sufix.'.key';
			$files['private'] = $dir.'priv_'.$sufix.'.key';
		} else {
			$files['public'] = $dir.'pub.key';
			$files['private'] = $dir.'priv.key';
		}

		if (($pubKey = $this->publicKey()) && ($privKey = $this->privateKey())){
			$pubHandler = fopen($files['public'],'w');
			if (!$pubHandler)
				return false;
			if ( !fwrite($pubHandler,$pubKey) )
				return false;
			fclose($pubHandler);
			$this->keys['public'] = $pubKey;

			$privHandler = fopen($files['private'],'w');
			if (!$privHandler)
				return false;
			if ( !fwrite($privHandler,$privKey) )
				return false;
			fclose($privHandler);
			$this->keys['private'] = $privKey;

			return $files;
		}

		return false;
	}

}
