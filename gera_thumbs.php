<?php
set_time_limit(0);

require_once('inc/config.php');
require_once('inc/mediaserver.php');
require_once('inc/util.php');

$util	 = new Util;
$media	 = new MediaServer($TAMANHOS, $EXTENSOES_VALIDAS);
$imagens = $media->listarImagens();

$util->fazChamadaMultiTaskParaProcessarImagens($imagens);
