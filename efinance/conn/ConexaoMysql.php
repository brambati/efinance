<?php
	/**
	 * <strong>Classe Conexão</strong>
	 * Esta classe faz a conexão com o banco de dados
	 * em qualquer tipo de SGBD basta alterar o drive de conexão
	 * @author Jonathan Moreira Brambati <brambati.moreira@gmail.com>
	 * @link https://www.jonathanmoreira.com.br
	 */
	class ConexaoMysql {
		
		/*
		 * Variaveis privadas da class
		 */
		private  	$drive   = 'mysql', 
					$host    = 'localhost', 
					$banco   = 'efinance', 
					$usuario = 'root', 
					$senha   = '';
		
		/*
		 * Variaveis protegidas da class
		 */		
		protected  $conex;
		
		/**
		 * Construtor da classe 
		 */
		public function __construct($db = null){
			
			try{

				$db = (!empty($db)) ? $db : $this->banco;
				
				# Atribui o objeto PDO à variável $conex.
				$this->conex = new PDO($this->drive.":host=".$this->host.";" ."dbname=".$db, $this->usuario, $this->senha);
				
				# Garante que o PDO lance exceções durante erros.
				$this->conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				 # Garante que os dados sejam armazenados com codificação UFT-8.
				$this->conex->exec('SET NAMES utf8');
			
			}catch(PDOException $e){
				
				# Então não carrega nada mais da página e mostra uma mensagem para o usuario.
				die("Erro ao conectar no banco de dados: " . $e->getMessage());
			}			
		}
		
		/**
		 * Garante uma única instância. 
		 * Se não existe uma conexão, criamos uma nova.
		 */
		public function dbCon() {
			
			# verifica se existe uma instância
			if(!$this->conex){
				
				# cria uma conexão
				new ConexaoMysql();
				
			} else {
				 # Retorna a conexão.
				return $this->conex;
			}
		}
		
		/**
		 * Destroi a conexão com o banco de dados
		 */
		public function __destruct(){	
			$this->conex = null;
		}
		
	}
