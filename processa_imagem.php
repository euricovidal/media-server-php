<?php
require_once('inc/config.php');
require_once('inc/mediaserver.php');
require_once('inc/util.php');

$id		 = end(explode('=', end($argv)));
$util	 = new Util;
$media	 = new MediaServer($TAMANHOS, $EXTENSOES_VALIDAS);
$arquivo = $util->verificaSeImagemExiste( DIR_TEMP . DIRECTORY_SEPARATOR . $id );

try {
	$media->criaEstruturaDeDiretoriosEImagem($arquivo);
} catch(Exception $e) {
    $log	= '[' . date('D M y H:i:s Y') . '] [error] Error on generate images - ' . $arquivo . "\n";
	$fp		= fopen(PATH . DIRECTORY_SEPARATOR . 'errors.log', 'a');
	fwrite($fp, $log);
	fclose($fp);
}
