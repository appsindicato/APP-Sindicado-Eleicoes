<?php

namespace Crypto;
use \FilePlugin;

abstract class Decrypt {

	/**
	 * Le um dado encriptado e retorna o seu conteúdo em texto plano
	 * @param  string $data       Dados encriptados
	 * @param  string $privateKey Chave privada RSA
	 * @return string             Dados em texto plano
	 */
	public function rsa(string $data = '',string $privateKey = ''){
		if (!$privateKey)
			throw new \InvalidArgumentException('Uma chave privada deve ser especificada');
		if (!$data)
			throw new \InvalidArgumentException('Sem dados para rodar Decrypt');

		openssl_private_decrypt($data, $result, $privateKey);

		return $result;
	}

	/**
	 * Busca a chave privada
	 * @param  int $id identificador único da urna
	 * @return string             Dados em texto plano
	 */
	public function key($id = null, $key, $secret, $bucket = 'appsindicato-data'){
		try {
			if (!$id)
				throw new \InvalidArgumentException('Invalid id');

			$s3 = new FilePlugin($key,$secret,$bucket);
			
			$get = $s3->get('urnas/'.$id.'/keys/priv.key');
			$privKey = (string) $get['Body'];

			return $privKey;

		}catch (\InvalidArgumentException $e){
			throw new \InvalidArgumentException('Sem dados para buscar a privKey');

		}
	}

	/**
	 * Le um dado encriptado usando um cipher e retorna seu conteúdo em texto plano
	 * @param  string $data      Dados encriptados
	 * @param  string $password  "Senha" utilizada para encriptar o dado
	 * @param  string $algorithm Algoritmo utilizado para encriptar o dado
	 * @param  string $iv        Salt utilizado para encriptar o dado
	 * @return string            Dados em texto plano
	 */
	public function cipher(string $data = '', string $password = '', string $algorithm = 'aes-256-cbc', string $iv = ''){
		if (!$password)
			throw new \InvalidArgumentException('Uma chave de decipher deve ser especificada');
		if (!$iv)
			throw new \InvalidArgumentException('O valor do vetor inicial (iv) deve ser especificado');
		if (!$data)
			throw new \InvalidArgumentException('Sem dados para rodar Decrypt');
		return openssl_decrypt($data, $algorithm, $password, 0, $iv);
	}

}
