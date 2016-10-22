<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR  . 'config.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'redis_server.php');
/**
 * Classe de Cache para armazenamento das imagens
 * Utilizando RedisServer
 */
class Cache {
	private $cache;

	/**
	 * Metodo construtor inicia a conexao
	 * @param string $host nome do host do Cache
	 * @param integer $porta numero da porta do Cache
	 * @return void
	 */
	public function __construct($host = CACHE_HOST, $porta = CACHE_PORTA) {
		if(!class_exists('RedisServer')) exit('REDIS_SERVER NOT FOUND');
		$this->cache = new RedisServer($host, $porta);
	}

	/**
	 * Atribui valor a uma variavel no Cache
	 * @param string $nome nome para a variavel
	 * @param string|integer|file $valor qualquer valor para ser atribuido
	 * @param integer $expiracao tempo de expiracao do cache
	 */
	public function atribui($nome, $valor, $expiracao = TEMPO_EXPIRACAO_CACHE) {
		$this->cache->Set($nome, $valor, $expiracao);
		return $valor;
	}

	/**
	 * Busca valor de uma variavel no Cache
	 * @param string $nome nome da variavel
	 * @return all valor da variavel armazenada
	 */
	public function busca($nome) {
		return $this->cache->Get($nome);
	}

	/**
	 * Apagar cache
	 * @param string $nome nome da variavel
	 * @return void
	 */
	public function apagar($nome) {
		$this->cache->Del($nome);
	}
}
