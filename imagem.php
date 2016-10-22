<?php
set_time_limit(0);

require_once('inc/config.php');
require_once('inc/util.php');
require_once('inc/mediaserver.php');
require_once('inc/cache.php');

$util		= new Util;
$cache		= new Cache;
$media		= new MediaServer($TAMANHOS, $EXTENSOES_VALIDAS);
$id			= isset($_GET['id'])		? $util->normalizaString($_GET['id'])	: null;
$width		= isset($_GET['width'])		? (int) $_GET['width']					: null;
$height		= isset($_GET['height'])	? (int) $_GET['height']					: null;
$nome		= $id . '_' . $width. '_' . $height;

if(!$id) exit('INFORME A IMAGEM');

if($imagem_cache = $util->pegaImagemDoCacheSeExistir($nome, $cache)) {
	exit($imagem_cache);
} else {
	exit($media->demanda($id, $nome, $width, $height, DIR_REPO, $cache));
}
