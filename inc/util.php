<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
/**
 * Funcoes em comum do sistema
 */
class Util {
	/**
	 * Remove caracteres especiais permitindo apenas letras e numeros
	 * @param string $texto
	 * @param string $permitidos caracteres permitidos
	 * @return string
	 */
	public function removeCaracteresEspeciais($texto, $permitidos = '') {
		return preg_replace('/[^a-zA-Z0-9(' . $permitidos . ')]/', '', trim($texto));
	}

	/**
	 * Retorna string normalizada
	 * @see removeCaracteresEspeciais()
	 * @param string $string texto a ser normalizado
	 * @param string $permitidos caracteres permitidos
	 * @return string em maiusculo
	 */
	public function normalizaString($string, $permitidos = null) {
		return $this->removeCaracteresEspeciais(strtoupper($string), $permitidos);
	}

	/**
	 * Retorna caminho absoludo baseado no id
	 * @param integer $id
	 * @return string
	 */
	public function retornaCaminhoDoDiretorioPeloId($id) {
		return (string) implode(DIRECTORY_SEPARATOR, str_split($id));
	}

	/**
	 * Retorna imagem com seu respectivo cabecalho
	 * @param string $imagem caminho absoludo da imagem
	 * @param string $tipo_imagem tipo da imagem, utilizado quando foi buscado do cache
	 * @return file com seu cabecalho
	 */
	public function headerImagem($imagem, $tipo_imagem = null) {
		if($tipo_imagem) {
			header('Content-type: ' . $tipo_imagem);
			return $imagem;
		} else {
			$atributos_imagem = getimagesize($imagem);
			header('Content-type: ' . $atributos_imagem['mime']);
			header('Content-transfer-encoding: binary');
			header('Content-length: ' . filesize($imagem));
			header('Cache-Control: cache, store');
			header('Expires: Mon, 01 Oct 2090 00:00:00 GMT');
			header('Pragma: cache');
			return file_get_contents($imagem);
		}
	}

	/**
	 * Verifica se a imagem existe
	 * @param string $imagem caminho absoludo da imagem
	 * @return string primeira imagem encontrada ou null caso nenhuma for encontrada
	 */
	public function verificaSeImagemExiste($imagem) {
		$lista_imagens = glob($imagem . '.*');
		return reset($lista_imagens);
	}

	/**
	 * Verifica se a imagem existe no Cache
	 * @param string $imagem caminho absoludo da imagem
	 * @return image | false caso a imagem nao exista
	 */
	public function verificaSeImagemExisteNoCache($imagem, Cache $cache) {
		$atributos_imagem	= pathinfo($imagem);
		$dados_imagem		= getimagesize($imagem);
		$mime_type			= end($dados_imagem);
		$nome				= $atributos_imagem['filename'];
		if($cache->busca('image_' . $nome)) {
			return $this->headerImagem($cache->busca('image_' . $nome), $cache->busca('type_image_' . $nome));
		} else {
			$imagem = $this->headerImagem($imagem);
			$cache->atribui('type_image_' . $nome, $mime_type);
			return $cache->atribui('image_' . $nome, $imagem);
		}
	}

	/**
	 * Exibe imagem padrao
	 * @param integer $width largura da imagem
	 * @param integer $height altura da imagem
	 * @param string $diretorio_imagem_padrao caminho do diretorio onde esta a imagem padrao
	 * @param Cache $cache instancia da classe Cache
	 * @param MediaServer $meida instancia da classe MediaServer
	 * @see headerImagem()
	 * @return imagem
	 */
	public function exibeImagemPadrao($width, $height, $dir_imagem_padrao = DIR_IMAGEM_PADRAO, Cache $cache = null, $media = false) {
		$nome = $width . '_' . $height;
		if($cache->busca('image_' . $nome)) {
			return $this->headerImagem($cache->busca('imagem_' . $nome), $cache->busca('type_image_' . $nome));
		} else {
			if(!$media) {
				require_once(__DIR__ . DIRECTORY_SEPARATOR . 'mediaserver.php');
				$media = new MediaServer($TAMANHOS, $EXTENSOES_VALIDAS);
			}
			$media->redimensiona($dir_imagem_padrao, 'imagem.jpg', $width, $height);
			$imagem_padrao	= $dir_imagem_padrao . DIRECTORY_SEPARATOR . 'imagem_' . $nome;
			$imagem_padrao	= $this->verificaSeImagemExiste($imagem_padrao);
			$dados_imagem	= getimagesize($imagem_padrao);
			$mime_type		= end($dados_imagem);
			$cache->atribui('type_image_' . $nome, $mime_type);
			return $cache->atribui('imagem_' . $nome, $this->headerImagem($imagem_padrao));
		}
	}

	/**
	 * Pega imagem do cache se existir
	 * @param string $nome caminho absoludo da imagem
	 * @param Cache $cache instancia da classe Cache
	 * @return Image | boolean
	 */
	public function pegaImagemDoCacheSeExistir($nome, Cache $cache) {
		if($cache->busca('image_' . $nome)) {
			return $this->headerImagem($cache->busca('image_' . $nome), $cache->busca('type_image_' . $nome));
		} else return false;
	}

	/**
	 * Percorre imagens do diretorio temporario e chama varias instancias do PHP (multi-task)
	 * @see processa_imagem.php
	 * @param array $imagens lista de imagens a serem importadas
	 * @return boolean
	 */
	public function fazChamadaMultiTaskParaProcessarImagens($imagens) {
		foreach($imagens as $image) {
			$atributos_imagem	= pathinfo($image);
			$id					= $atributos_imagem['filename'];

			exec(PATH_PHP . ' -q processa_imagem.php id=' . $id . ' > /dev/null 2>&1 &');
		}
	}
}
