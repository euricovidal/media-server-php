<?php
/**
 * Configuracoes/Parametros para o sistema
 * @author Eurico Vidal <euricovidal@gmail.com>
 * @author Henrique Pementel
 * @version 1.0
 */

/**
 * Parametro que define o caminho completo da raiz do aplicativo
 * @return string PATH
 */
define('PATH', __DIR__ . DIRECTORY_SEPARATOR . '..');

/**
 * Parametro que define o caminho do PHP
 * @return string PATH_PHP
 */
define('PATH_PHP', '/usr/bin/php');

/**
 * Parametro que define o tempo de expiracao do cache em segundos, definido 5 dias = 432000
 * @return integer CACHE_TEMPO_EXPIRACAO
 */
define('TEMPO_EXPIRACAO_CACHE', 432000);

/**
 * Parametro que define o host do Cache
 * @return string CACHE_HOST
 */
define('CACHE_HOST', 'localhost');

/**
 * Parametro que define a porta de conexao do Cache
 * @return integer CACHE_PORTA
 */
define('CACHE_PORTA', 6379);

/**
 * Parametro que define a qualidade que os thumbs serao gerados
 * @return integer QUALIDADE
 */
define('QUALIDADE', 70);

/**
 * Parametro que define o caminho do diretorio temporario
 * @return string DIR_TEMP
 */
define('DIR_TEMP', PATH . DIRECTORY_SEPARATOR . 'temporario');

/**
 * Parametro que define o caminho do diretorio definitivo (repositorio)
 * @return string DIR_REPO
 */
define('DIR_REPO', PATH . DIRECTORY_SEPARATOR . 'repositorio');

/**
 * Parametro que define as permissoes para os diretorios criados
 * @return integer PERMISSAO_DO_DIRETORIO
 */
define('PERMISSAO_DO_DIRETORIO', 0777);

/**
 * Parametro que define o diretorio da imagem padrao (imagem.jpg)
 * Exibida quando a imagem solicitada nao foi encontrada
 * @return string DIR_IMAGEM_PADRAO
 */
define('DIR_IMAGEM_PADRAO', DIR_REPO . DIRECTORY_SEPARATOR . 'imagem_padrao');

/**
 * Variavel que define os tipos de imagem validas para a importacao
 * Necessario informar a extensao em MAIUSCULO
 * @return array EXTENSOES_VALIDAS [string EXTENSAO, string MIME_TYPE]
 */
$EXTENSOES_VALIDAS = array(
	'JPG'  => 'image/jpeg',
	'JPEG' => 'image/jpeg',
	'PNG'  => 'image/png'
);

/**
 * Variavel que define os tamanhos a serem gerados
 * Caso nao informado na classe MediaServer existe uma constante com um valor default de 800x600
 * @return array TAMANHOS (integer WIDTH, integer HEIGHT)
 */
$TAMANHOS = array(array(800, 600), array(320, 240), array(160, 120));
