<?php

/**
 * Description of Requete
 *
 * @author Sparks
 * @version 0.0.1
 */
class Requete extends Instance {
	
	public static $_version = '0.0.1';
	
	/* -------------------------------------------- */
	/* -------------------------------------------- */

	private static $requetes = array();
	private static $nbRequetes = 0;
	private static $nbRequeteEchec = 0;
	private static $timeExecutionRequete = 0;

	/* -------------------------------------------- */
	/* -------------------------------------------- */
	// select : 1, update : 2, insert : 3, remove : 4
	protected $type = null;
	protected $id = 0;
	
	protected $timeExecution = 0;
	
	protected $requeteSTR = '';
	
	protected $colonnes = array();
	protected $getOne = false;
//	protected $numType = 0;
	protected $tables = array();
	protected $baseCible = null;
	protected $conditions = array();
	protected $donnees = array();
	protected $order = array();
	protected $limite = array();
	protected $groupBy = array();

	/* -------------------------------------------- */
	/* -------------------------------------------- */
	
	protected $debug = false;
	protected $backTrace = null;

	/* -------------------------------------------- */
	/* -------------------------------------------- */

	/**
	 * Constructeur de l'objet Requete.
	 * 
	 * @param int $type Le type de requete (select : 1, update : 2, insert : 3, remove : 4)
	 * @param mixed $table String ou tableau de String contenant le nom des tables
	 */
	function __construct($type = 1, $table = null) {
		$this->type = $type;
		$this->id = uniqid();
		
		$this->backTrace = debug_backtrace();

		self::$requetes[$this->id] = $this;
		
		if($table) {
			$this->table($table);
		}
		
		parent::__construct();
	}

	/*
	 * Méthodes publics permettant l'utilisation de la classe Requete par "l'utilisateur"
	 */
	//<editor-fold defaultstate="collapsed" desc=" Methodes public ">
	
	/**
	 * Permet de definir la ou les tables visées par la requète
	 * @param String $tables String contenant le nom de la table
	 * @return \Requete L'objet en cours
	 */
	public function table($tables) {
		if(is_array($tables)) {
			$this->tables($tables);
		}
		elseif(is_string($tables)) {
			if(Cache::tableExist($tables, $this->baseCible)) {
				$this->baseCible = Cache::getBase($tables);
				$this->tables[] = $tables;
			}
			else if($this->baseCible) {
				$this->edebug('La table "' . $tables . '" n\'existe pas sur la base "' . $this->baseCible . '".');
			}
			else 					{
				$this->edebug('La table "' . $tables . '" n\'existe pas.');
			}
			
		}
		return $this;
	}
	
	/**
	 * Permet de definir la ou les tables visées par la requète
	 * @param Array $tables tableau de String contenant les noms des tables
	 * @return \Requete L'objet en cours
	 */
	public function tables($tables) {
		if(is_array($tables) && !empty($tables)) {
			foreach($tables as $table) {
				$this->table($table);
			}
		}
		elseif(is_string($tables)) {
			$this->table($tables);
		}
		return $this;
	}
	
	/**
	 * Permet de definir une condition à la requete (WHERE)
	 * @param String $colle L'operateur de collage (AND, OR, ...) 
	 * @param String $colonne La colonne concernée par la condition
	 * @param String $operateur L'operateur de verification (=, >, <, ...)
	 * @param String $valeur La valeur à verifier
	 * @return \Requete L'objet en cours
	 */
	public function condition($colle, $colonne, $operateur, $valeur) {
		$this->conditions[] = array(
			'colle' => $colle,
			'colonne' => $colonne,
			'operateur' => $operateur,
			'valeur' => $valeur
		);
		return $this;
	}
	/**
	 * Racourci pour créer plusieurs conditions d'un coup, avec les opérateurs AND et = (AND xxx = 'xxx')
	 * @param array $conditions Le tableau associatif (cle => valeur)
	 * @return \Requete L'objet en cours
	 */
	public function conditionArray($conditions) {
		if(!empty($conditions) && is_array($conditions)) {
			foreach($conditions as $cle => $valeur) {
				$this->condition('AND', $cle, '=', $valeur);
			}
		}

		return $this;
	}
	/**
	 * Alias de conditionArray : Racourci pour créer plusieurs conditions d'un coup, avec les opérateurs AND et = (AND xxx = 'xxx')
	 * @param array $conditions Le tableau associatif (cle => valeur)
	 * @return \Requete L'objet en cours
	 */
	public function conditions($conditions) {
		return $this->conditionArray($conditions);
	}
	/**
	 * Permet de definir une condition manuellement (dans le cas de conditions complexe par exemple)
	 * @param String $condition Chaine contenant la condition pré écrite
	 * @return \Requete L'objet en cours
	 */
	public function conditionString($condition) {
		$this->conditions[] = $condition;
		return $this;
	}

	/**
	 * Permet de definir les données ciblé par la requete (données a récuperer/modifier/inserer)
	 * @param array $donnees Le tableau de données (clé => valeur si ce n'est pas une requete select)
	 * @return \Requete L'objet en cours
	 */
	public function donnees($donnees) {
		if(is_array($donnees) && !empty($donnees)) {
			foreach($donnees as $colonne => $valeur) {
				if($this->type == 1) {
					$this->donnee($valeur);
				}
				else {
					$this->donnees[$colonne] = $valeur;
				}
			}
			
		}
		elseif(is_string($donnees)) {
			$this->donnee($donnees);
		}
		return $this;
	}
	
	/**
	 * Permet de definir une à une les données à récupérer (cas d'une requete select uniquement)
	 * @param string $donnees La donnée à récupérer
	 * @return \Requete L'objet en cours
	 */
	public function donnee($donnees) {
		if(is_string($donnees)) {
			$this->donnees[] = $donnees;
		}
		elseif(is_array($donnees)) {
			$this->donnees($donnees);
		}
		return $this;
	}

	/**
	 * Permet de definir l'ordre de try de la requete (ORDER BY)
	 * @param type $donnees Tableau d'informations array('colonne' => [la colonne visée], 'ordre' => 'ASC', 'verifExiste' => true)
	 * @return \Requete L'objet en cours
	 */
	public function orders($donnees = array(
			'colonne' => null,
			'ordre' => 'ASC',
			'verifExiste' => true,
		)) {
		$this->order[] = $donnees;
		return $this;
	}
	/**
	 * Permet de definir l'ordre de try de la requete (ORDER BY)
	 * @param string $donnees La colonne ciblé par le tri
	 * @return \Requete L'objet en cours
	 */
	public function order($donnees) {
		if(is_array($donnees)) {
			self::orders($donnees);
		}
		else {
			$this->order[] = array(
				'colonne' => $donnees,
				'ordre' => 'ASC',
				'verifExiste' => true
			);
		}
		return $this;
	}
	/**
	 * Permet de definir l'ordre de try de la requete (ORDER BY)
	 * @param string $donnees La colonne visée par le tri
	 * @param string $ordre Ordre du tri (ASC / DESC)
	 * @param boolean $verif Verifie ou non si la colonne existe
	 * @return \Requete L'objet en cours
	 */
	public function orderBy($donnees, $ordre = 'ASC', $verif = true) {
		$this->order[] = array(
			'colonne' => $donnees,
			'ordre' => $ordre,
			'verifExiste' => $verif
		);
		return $this;
	}
	
	/**
	 * Permet de definir le paramètre LIMIT de la requete
	 * @param int $start l'offset de récupération
	 * @param int $length Le nombre maximum de ligne a récuperer
	 * @return \Requete L'objet en cours
	 */
	public function limite($start, $length) {
		$this->limite = array(
			'start' => $start,
			'length' => $length
		);
		return $this;
	}
	
	/**
	 * Permet de definir le(s) groupement(s) des resultats de la requete (GROUP BY)
	 * @param mixed $donnees la ou les colonne(s) visée's)
	 * @return \Requete L'objet en cours
	 */
	public function group($donnees) {
		if(is_array($donnees)) {
			if(!empty($donnees)) {
				foreach($donnees as $valeur) {
					$this->groupBy[] = $valeur;
				}
			}
		}
		elseif(is_string($donnees)) {
			$this->groupBy[] = $donnees;
		}
		return $this;
	}
	/**
	 * ALIAS OF group
	 * @param mixed $donnees
	 * @return \Requete L'objet en cours
	 */
	public function groupBy($donnees) {
		return $this->group($donnees);
	}
	
	//</editor-fold>

	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */

	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */

	/**
	 * Execute une requete sans passer par la classe et les verifications (Cas de requètes complèxes)
	 * @param sting $requete la requete a éxecuter
	 * @param array $values Le tableau de valeurs
	 * @param mixed $fetch_mode Le type de récupération (PDO::FETCH_...)
	 * @param boolean $fetchOne true pour ne récuperer que le premier résultat
	 * @return mixed null/false en cas de résultat vide ou d'erreur, un tableau de résultat(s)
	 */
	public static function query($requete, $values = array(), $fetch_mode = PDO::FETCH_ASSOC, $fetchOne = false) {
		$db = BaseDeDonnee::Connect(/* nom de la bdd */);
		
		$return = null;
		
		self::$nbRequetes++;
		$informaionsRequete = array(
			'requete' => $requete,
			'echec' => false,
			'time' => 0
		);
		
		$timeBefore = microtime(true);
		
		try {

			$results = $db->getConnexion()->prepare($requete);
			$results->execute($values);

			if($fetch_mode) {
				$results->setFetchMode($fetch_mode);
				if($fetchOne) {
					$return = $results->fetch();
				}
				else {
					$return = $results->fetchAll();
				}
				
			}

			$results->closeCursor();
		}
		catch(Exception $e) {
			self::$nbRequeteEchec++;
			$informaionsRequete['echec'] = $e;
			$this->edebug($e);
		}
		
		$timeAfter = microtime(true);

		//$this->timeExecution = $timeAfter - $timeBefore;
		$informaionsRequete['time'] = $timeAfter - $timeBefore;
		self::$timeExecutionRequete += $informaionsRequete['time'];

		self::$requetes[] = $informaionsRequete;
		
		return $return;
	}

	/**
	 * Lance l'execution de la requete en cours.
	 * Une erreur bloquante est créée den cas d'exception.
	 * @return mixed false si echec ou resultat vide, array si un resultat est trouvé
	 */
	public function execute() {
		$string = $this->createRequete();
		
		if($string) {
			self::$nbRequetes++;
			$this->requeteSTR = $string;

			$timeBefore = microtime(true);

			$db = BaseDeDonnee::Connect($this->baseCible);

			try {
				$retour = $this->_executeType($db, $string, $this->marqueurs);
			}
			catch(Exception $e) {
				self::$nbRequeteEchec++;
				die('Erreur : ' . $e->getMessage());
			}

			$timeAfter = microtime(true);

			$this->timeExecution = $timeAfter - $timeBefore;
			self::$timeExecutionRequete += $this->timeExecution;
			
			return $retour;
		}
	}

	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */

	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	//<editor-fold defaultstate="collapsed" desc=" Methodes protegées (Construction de la requete) ">
	protected function createRequete() {
		
		$this->marqueurs = array();

		if(!empty($this->tables)) {
			foreach($this->tables as $table) {
				$this->colonnes[$table]['colonnes'] = Cache::getColonnes($table);
				$this->colonnes[$table]['primary'] = Cache::getPrimaryKey($table);
			}
		}
		else {
			$this->edebug('Vous devez choisir au moins une table');
			return false;
		}

		$requeteSTR = $this->_createRequete();

		$requeteSTR .= ';';

		return $requeteSTR;
	}
	
	protected function _donnees() {
		$requeteSTR = '';
		if($this->type == 1) {
			if(!empty($this->donnees)) {

				$first = true;
				foreach($this->donnees as $valeur) {
					$colonneE = $this->colonneExiste($valeur);
					if($colonneE['existe']) {
						if(!$first) {
							$requeteSTR .= ', ';
						}

						if($colonneE['function']) {
							$requeteSTR .= '' . $valeur . '';
						}
						else {
							$requeteSTR .= '`' . $valeur . '`';
						}

						$first = false;
					}
				}
			}
			else {
				$requeteSTR .= '*';
			}
		}
		else {
			if(!empty($this->donnees)) {
				$first = true;
				foreach($this->donnees as $cle => $valeur) {
					$colonneE = $this->colonneExiste($cle);
					if($colonneE['existe']) {
						if(!$first) {
							$requeteSTR .= ', ';
						}
						
						$requeteSTR .= '`' . $cle . '` = :' . $this->marqueur($valeur) . '';
						$first = false;
					}
				}
			}
			else {
				$this->edebug('Il n\'y a aucunes données de renseigné');
			}
		}
		return $requeteSTR;
	}

	protected function _order() {
		$orderStr = '';
		$first = true;

		if(!empty($this->order)) {
			foreach($this->order as $order) {

				if(is_array($order)) {
					if(!isset($order['verifExiste'])) {
						$order['verifExiste'] = true;
					}
					
					if($order['verifExiste']) {
						$colonneE = $this->colonneExiste($order['colonne']);

						if(!$colonneE['existe']) {
							continue;
						}
					}

					if($order['ordre'] != 'ASC' && $order['ordre'] != 'DESC') {
						$order['ordre'] = 'ASC';
					}
				}

				if(!$first) {
					$orderStr .= ', ';
				}
				else {
					$orderStr .= ' ORDER BY ';
				}
				
				if(is_array($order)) {
					if($order['verifExiste']) {
						$orderStr .= '`' . $order['colonne'] . '` ' . $order['ordre'];
					}
					else {
						$orderStr .= '' . $order['colonne'] . ' ' . $order['ordre'];
					}
				}
				$first = false;
			}
		}

		return $orderStr;
	}

	protected function _group() {
		$groupStr = '';
		$first = true;

		if(!empty($this->groupBy)) {
			foreach($this->groupBy as $group) {

				$colonneE = $this->colonneExiste($group);
				if(!$colonneE['existe']) {
					continue;
				}

				if(!$first) {
					$groupStr .= ', ';
				}
				else {
					$groupStr .= ' GROUP BY ';
				}

				$groupStr .= '`' . $group . '`';
				$first = false;
			}
		}

		return $groupStr;
	}

	protected function _limite() {
		$limiteStr = '';
		
		$this->edebug($this->limite);

		if(!empty($this->limite['start']) || isset($this->limite['start']) && $this->limite['start'] == 0) {
			$limiteStr .= ' LIMIT ' . $this->limite['start'];
			if(!empty($this->limite['length'])) {
				$limiteStr .= ', ' . $this->limite['length'];
			}
		}

		return $limiteStr;
	}

	protected function _conditions() {
		$conditionStr = '';

		$first = true;

		if(!empty($this->conditions)) {
			$conditionStr .= ' WHERE ';
			foreach($this->conditions as $condition) {
				$this->edebug($condition);
				// Si la colonne n'existe pas on passe...
				// Si c'est une chaine de caractère, on ne verifie pas l'existance
				if(is_array($condition)) {
					$colonneE = $this->colonneExiste($condition['colonne']);
					$this->edebug($colonneE);
					if(!$colonneE['existe']) {
						continue;
					}
				}

				if(!$first) {
					$conditionStr .= ' ';

					if(is_array($condition)) {
						$conditionStr .= $condition['colle'];
					}
					else {
						//$conditionStr .= 'AND';
					}

					$conditionStr .= ' ';
				}


				if(is_array($condition)) {
					if($colonneE['function']) {
						$conditionStr .= '' . $condition['colonne'] . ' ' . $condition['operateur'] . ' :' . $this->marqueur($condition['valeur']) . '';
					}
					else {
						$conditionStr .= '`' . $condition['colonne'] . '` ' . $condition['operateur'] . ' :' . $this->marqueur($condition['valeur']) . '';
					}
				}
				else {
					$conditionStr .= $condition;
				}
				$first = false;
			}
		}

		return $conditionStr;
	}
	//</editor-fold>
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */

	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */

	protected function marqueur($valeur) {
		$nomMarqueur = 'marqueur' . (count($this->marqueurs));
		$this->marqueurs[$nomMarqueur] = $valeur;

		return $nomMarqueur;
	}
	
	protected function colonneExiste($colonne, $function = false) {

		// count(distinct(ip))

		$colonne = trim($colonne);

		if(strpos($colonne, 'AS') !== false) {
			$this->edebug('AS');
			list($nomColonne, $toVar) = explode('AS', $colonne);

			if($nomColonne == '*') {
				return array('existe' => true, 'function' => true);
			}
			
			return $this->colonneExiste(trim($nomColonne), true);
		}
		elseif(strpos($colonne, '.') !== false) {
			$this->edebug('.');
			list($table, $nomColonne) = explode('.', $colonne);

			if($nomColonne == '*') {
				return array('existe' => true, 'function' => true);
			}

			if(!empty($this->colonnes[$table])) {
				if(in_array($nomColonne, $this->colonnes[$table]['colonnes'])) {
					return array('existe' => true, 'function' => true);
				}
			}
			
			return $this->colonneExiste($nomColonne, true);
		}
		elseif(strpos($colonne, '(') !== false) {
			$this->edebug('(');
			$nbFunction = substr_count($colonne, '(');

			$arrayColonne = self::mexplode(array('(', ')'), $colonne);

			return $this->colonneExiste($arrayColonne[$nbFunction], true);
		}
		elseif(strpos($colonne, ' ') !== false) {
			$this->edebug('SPACE');
			list($function, $nomColonne) = explode(' ', $colonne);

			return $this->colonneExiste($nomColonne, true);
		}
		else {
			if($colonne == '*') {
				return array('existe' => true, 'function' => $function);
			}

			foreach($this->colonnes as $modules) {
				foreach($modules['colonnes'] as $col) {
					if($col == $colonne) {
						return array('existe' => true, 'function' => $function);
					}
				}
			}
		}

		return array('existe' => false, 'function' => $function);
	}

	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */

	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	/*	 * *********************************************************************** */
	
	/**
	 * Retourne le nombre total de requete (efféctué ou non)
	 * @return int le nombre de requetes total
	 */
	public static function getNbRequete() {
		return count(self::$requetes);
	}

	/**
	 * Retourne le nombre de requetes qui ont échoué
	 * @return int le nombre de requetes qui ont échoué
	 */
	public static function getNbRequeteEchec() {
		return self::$nbRequeteEchec;
	}
	
	/**
	 * Retourne le nombre de requètes reussi
	 * @return int le nombre de requètes reussi
	 */
	public static function getNbRequeteReussi() {
		return self::getNbRequete() - self::$nbRequeteEchec;
	}

	/**
	 * Retourne le temps total d'execution (en millisecondes) des requètes
	 * @param int $decimales
	 * @return float le temps d'éxecution des requètes
	 */
	public static function getTempsExecution($decimales = 3) {
		return number_format(self::$timeExecutionRequete, (int) $decimales);
	}

	/** *********************************************************************** */
	/** *********************************************************************** */
	/** *********************************************************************** */

	/** *********************************************************************** */
	/** *********************************************************************** */
	/** *********************************************************************** */
	
	// Fonctions racourcies
	
	/**
	 * Permet de selectionner toutes lignes qui remplissent la/les condition(s) de la table indiqué
	 * @param string $table Le nom de la table
	 * @param string $select NULL pour selectionner tout, sinon le selecteur sql (nom, prenom, age, sexe, ...)
	 * @param mixed $line Si c'est une chaine, on test l'égalité de la valeur du champs avec le paramètre $val. Si c'est un tableau associatif, on prend la clé comme champs et on test l'égalité avec la valeur
	 * @param string $val Si $line est une chaine, on verifie l'égalité du champs avec cette valeur
	 * @param boolean $one Default = false. Indique si on ne prend que la première ligne ou non
	 * 
	 * @return mixed false si aucune ligne n'est retourné, sinon un tableau ayant pour index les colonnes selectionnées
	 */
	public static function select_all($table, $select = null, $line = null, $val = null, $one = false, $debug = false) {
		$requete = self::select($table);
		$requete->donnee($select);

		if($one) {
			$requete->getOne();
		}

		if(is_array($line)) {
			$requete->conditionArray($line);
		}
		elseif($line) {
			$requete->condition('AND', $line, '=', $val);
		}
		
		if($debug) {
			$requete->debug();
		}

		return $requete->execute();
	}

	/**
	 * Permet de selectionner la première ligne qui remplis la/les condition(s) de la table indiqué
	 * @param string $table Le nom de la table
	 * @param string $select NULL pour selectionner tout, sinon le selecteur sql (nom, prenom, age, sexe, ...)
	 * @param mixed $line Si c'est une chaine, on test l'égalité de la valeur du champs avec le paramètre $value. Si c'est un tableau associatif, on prend la clé comme champs et on test l'égalité avec la valeur
	 * @param string $value Si $line est une chaine, on verifie l'égalité du champs avec cette valeur
	 * 
	 * @return mixed false si aucune ligne n'est retourné, sinon un tableau ayant pour index les colonnes selectionnées
	 */
	public static function select_one($table, $select = null, $line = null, $value = null, $debug = false) {
		return self::select_all($table, $select, $line, $value, true, $debug);
	}
	
	/**
	 * Permet de créer une nouvelle instance de la classe select
	 * @param mixed $table La ou les tables concernées (tableau de String ou String)
	 * @return \Select
	 */
	public static function select($table = null) {
		return new Select($table);
	}

	/**
	 * Permet de créer une nouvelle instance de la classe update
	 * @param type $table La ou les tables concernées (tableau de String ou String)
	 * @return \Update
	 */
	public static function update($table = null) {
		return new Update($table);
	}

	/**
	 * Permet de créer une nouvelle instance de la classe insert
	 * @param type $table La ou les tables concernées (tableau de String ou String)
	 * @return \Insert
	 */
	public static function insert($table = null) {
		return new Insert($table);
	}

	/**
	 * Permet de créer une nouvelle instance de la classe delete
	 * @param type $table La ou les tables concernées (tableau de String ou String)
	 * @return \Delete
	 */
	public static function remove($table = null) {
		return new Delete($table);
	}
	
	/** *********************************************************************** */
	/** *********************************************************************** */
	/** *********************************************************************** */

	/** *********************************************************************** */
	/** *********************************************************************** */
	/** *********************************************************************** */
	
	/**
	 * Permet d'afficher les informations de debugage de la requete (Informations en cours de completion)
	 */
	public function debug() {
		$this->debug = true;
		
		$positionHorsClass = 0;
		while($this->backTrace[$positionHorsClass]['function'] == '__construct') {
			$positionHorsClass++;
		} 
		
		$this->edebug('Requete '.$this->id.'. Type : '.$this->type.' ('.get_class($this).')');
		$this->edebug('Appellée dans : '.$this->backTrace[$positionHorsClass]['file'].' à la ligne '.$this->backTrace[$positionHorsClass]['line']);
		$this->edebug($this);
		$this->edebug($this->createRequete());
		
		$this->debug = false;
	}
	
	protected function edebug($var) {
		if($this->debug) {
			echo '<pre>' . print_r($var, true) . '</pre>';
		}
	}
	
	protected static function mexplode($delimiters, $string) {
		$ready = str_replace($delimiters, $delimiters[0], $string);
		$launch = explode($delimiters[0], $ready);
		return  $launch;
	}

}