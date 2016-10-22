<?php
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'config.php');
/**
 * Classe principal do Media Server
 */
class MediaServer {

	/**
	 * Nome da imagem principal (ou ID)
	 * @type string $nome_imagem
	 * @access public
	 */
	public $nome_imagem;
	private $qualidade	= QUALIDADE;
	private $tamanhos	= array(array(800, 600));
	private $extensoes_validas;

	/**
	 * Construtor da CLASSE
	 * @param array $tamanhos dimensoes das imagens
	 * @param array $extensoes extensoes de imagens validas
	 * @return void
	 */
    public function __construct($tamanhos, $extensoes) {
	    $this->tamanhos = (array) $tamanhos;
	    $this->extensoes_validas = (array) $extensoes;
    }

	/**
	 * Lista imagens de um determinado diretorio
	 * @param string $diretorio caminho do diretorio
	 * @return array $lista_imagens
	 */
    public function listarImagens($diretorio = DIR_TEMP) {
		$extensoes		= $this->concatenaExtensoes($this->extensoes_validas, $this->nome_imagem);
		$imagens		= glob($diretorio . DIRECTORY_SEPARATOR . '{' . $extensoes . '}', GLOB_BRACE);
		$lista_imagens	= array();

		foreach($imagens as $imagem){
			$dados_imagem = getimagesize($imagem);
			$mime		  = end($dados_imagem);
			if(array_search($mime, $this->extensoes_validas)) {
				$lista_imagens[] = $imagem;
			}
		}
		return $lista_imagens;
    }

    /**
     * Cria a estrutura de pastas e move imagens do diretorio temporario
	 * @param string $origem diretorio onde se encontram as imagens
	 * @param string $destino diretorio para onde as imagens serao movidas
	 * @return boolean
     */
    public function moveImagens($origem = DIR_TEMP, $destino = DIR_REPO) {
		$imagens = (array) $this->listarImagens();
		if(empty($imagens)) return false;

		foreach($imagens as $imagem) {
			if(!$this->criaEstruturaDeDiretoriosEImagem($imagem)) {
				return false;
			}
		}
		return true;
	}

    /**
     * Redimensiona e salva a imagem redimensionada nos tamanhos especificados
     * Caso não seja informado um dos tamanhos é calculada a proporção com base no tamanho informado e o tamanho original
	 * @param string $caminho diretorio onde se encontra a imagem e para onde sera salva a imagem redimensionada
	 * @param string $imagem nome da imagem original
	 * @param integer $largura largura para qual a imagem sera redimensionada
	 * @param integer $altura altura para qual a imagem sera redimensionada
	 * @param integer $permissoes para a criacao dos diretorios
	 * @return image imagem redimensionada
     */
    public function redimensiona($caminho, $imagem, $largura, $altura, $permissoes = PERMISSAO_DO_DIRETORIO) {
		$caminho_imagem		= $caminho . DIRECTORY_SEPARATOR . $imagem;
		$atributos_imagem	= array_merge(pathinfo($caminho_imagem), getimagesize($caminho_imagem));
		$nome_novo			= $atributos_imagem['filename'] . '_' . $largura . '_' . $altura;
		$mime_type			= $atributos_imagem['mime'];
		$extensao			= $atributos_imagem['extension'];
		$largura_original	= $atributos_imagem[0];
		$altura_original	= $atributos_imagem[1];

		if( empty($altura) ) {
			$altura = ($largura * $altura_original) / $largura_original;
		}
		if( empty($largura) ) {
			$largura = ($largura_original * $altura) / $altura_original;
		}

		switch( $mime_type ) {
			case 'image/png':
				$imagem_original = imagecreatefrompng($caminho_imagem);
				break;
			case 'image/jpeg':
			default:
				$imagem_original = imagecreatefromjpeg($caminho_imagem);
				break;
		}

		$nova_imagem = imagecreatetruecolor($largura, $altura);
		imagecopyresampled($nova_imagem, $imagem_original, 0, 0, 0, 0, $largura, $altura, $largura_original, $altura_original);

		$novo_caminho = $caminho . DIRECTORY_SEPARATOR . $nome_novo . '.' . $extensao;

		switch( $mime_type ) {
			case 'image/png':
				return imagepng($nova_imagem, $novo_caminho);
				break;
			case 'image/jpeg':
			default:
				return imagejpeg($nova_imagem, $novo_caminho, $this->qualidade);
				break;
		}
	}

	/**
	 * Cria estrutura de diretorios e move a imagem do diretorio temp para o respositorio
	 * @param string $destino caminho onde a estrutura de diretorios deve ser gerada
	 * @param string $imagem imagem com seu caminho absoludo
	 * @param integer $permissoes para a criacao dos diretorios
	 * @return void
	 */
	public function criaEstruturaDeDiretoriosEImagem($imagem, $destino = DIR_REPO, $permissoes = PERMISSAO_DO_DIRETORIO) {
		require_once(__DIR__ . DIRECTORY_SEPARATOR . 'util.php');
		$util  = new Util;

		$atributos_imagem	= pathinfo($imagem);
		$extensao			= $atributos_imagem['extension'];
		$nome_imagem		= $util->normalizaString($atributos_imagem['filename']);

		if(empty($nome_imagem)) return false;

		$destino .=  DIRECTORY_SEPARATOR;
		$caminho  = '';

		try {
			foreach(str_split($nome_imagem) as $caracter){
				$caminho .= $caracter . DIRECTORY_SEPARATOR;
				if(!is_dir($destino . $caminho)) {
					mkdir($destino . $caminho, $permissoes, true);
				}
			}
			$destino	.= $caminho;
			$nova_imagem = $destino . $atributos_imagem['basename'];

			if(file_exists($nova_imagem)) unlink($nova_imagem);
			if(copy($imagem, $nova_imagem)) {
				unlink($imagem);
				$this->limparDiretorioMenosImagemPadrao($destino);
				foreach($this->tamanhos as $tamanho) {
					$this->redimensiona($destino, $atributos_imagem['basename'], reset($tamanho), end($tamanho));
				}
			}
		} catch (Exception $e) {
			#TODO implementar sistema de log
			return false;
		}
		return true;
	}

	/**
	 * Exibe a imagem sobre demanda com base nas requisições feitas
	 * @param string $id id da imagem
	 * @param string $imagem imagem com seu caminho absoludo
	 * @param integer $largura tamanho da largura da imagem desejada
	 * @param integer $altura tamanho da altura da imagem desejada
	 * @param Cache $cache instancia do cache
	 * @return image/jpg|image/png
	 */
	public function demanda($id, $nome, $largura, $altura, $diretorio = DIR_REPO, Cache $cache) {
		require_once(__DIR__ . DIRECTORY_SEPARATOR . 'util.php');
		$util = new Util;

		$diretorio	= $diretorio . DIRECTORY_SEPARATOR . $util->retornaCaminhoDoDiretorioPeloId($id);
		$imagem		= $util->verificaSeImagemExiste($diretorio . DIRECTORY_SEPARATOR . $nome);

		if(file_exists($imagem)) {
			return $util->verificaSeImagemExisteNoCache($imagem, $cache);
		} else {
			$tamanhos = array($largura, $altura);
			if(array_search($tamanhos, $this->tamanhos) === false) $this->tamanhos = array_merge($this->tamanhos, array($tamanhos));
			$this->nome_imagem = $id;
			// Verifica se imagem existe no temporario
			if($this->moveImagens()) {
				$imagem = $util->verificaSeImagemExiste($diretorio . DIRECTORY_SEPARATOR . $nome);
				if(file_exists($imagem)) {
					return $util->verificaSeImagemExisteNoCache($imagem, $cache);
				}
			} else {
				$imagem_original = $util->verificaSeImagemExiste($diretorio . DIRECTORY_SEPARATOR . $id);
				// Verifica se imagem original existe no repositorio
				if(file_exists($imagem_original)) {
					$atributos_imagem_original = pathinfo($imagem_original);
					$imagem_original = $atributos_imagem_original['filename'] . '.' . $atributos_imagem_original['extension'];
					$this->redimensiona($diretorio, $imagem_original, $largura, $altura);
					$imagem = $util->verificaSeImagemExiste($diretorio . DIRECTORY_SEPARATOR . $nome);
					return $util->verificaSeImagemExisteNoCache($imagem, $cache);
				} else return $util->exibeImagemPadrao($largura, $altura, DIR_IMAGEM_PADRAO, $cache, $this);
			}
		}
	}

	/**
	 * Concatena extensoes com *.EXTENSAO,*.EXTENSAO2
	 * @param array $extensoes
	 * @param string $nome_imagem se nao possui valor seleciona todos
	 * @return string extensoes concatenadas
	 */
	private function concatenaExtensoes($extensoes, $nome_imagem = '*'){
		$nome_imagem = empty($nome_imagem) ? '*' : $nome_imagem;
		$extensoes = array_keys((array) $extensoes);
		foreach($extensoes as $extensao) {
			$extensoes_tratadas[] = $nome_imagem . '.' . $extensao;
		}
		return implode(',', $extensoes_tratadas);
	}

	/**
	 * Apaga imagens ja existentes no diretorio
	 * @param string $diretorio
	 * @return void
	 */
	private function limparDiretorioMenosImagemPadrao($diretorio) {
		require_once(__DIR__ . DIRECTORY_SEPARATOR . 'cache.php');
		$cache = new Cache;

		$imagens = glob($diretorio . DIRECTORY_SEPARATOR . '*_*{' . $extensoes . '}', GLOB_BRACE);
		foreach($imagens as $imagem) {
			$atributos_imagem	= pathinfo($imagem);
			$nome				= $atributos_imagem['filename'];
			$cache->apagar('type_image_' . $nome);
			$cache->apagar('image_' . $nome);
			unlink($imagem);
		}
	}
}
