<?php

/**
 * @author sparks
 */
class BaseDeDonnee extends Instance {
	
	public $base = null;
	private static $_alreadyConnected = array();
	
	function __construct($base) {
		
		try {
			$pdo_options = array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
			);

			$this->base = new PDO('mysql:dbname='.$base['name'].';host='.$base['hote'], $base['user'], $base['pass'], $pdo_options);
			self::$_alreadyConnected[$base['name']] = $this;
		}
		catch (Exception $e) {
			echo '<pre>'.print_r($e, true).'</pre>';
			die('Erreur : ' . $e->getMessage());
		}
	}
	
	function getConnexion() {
		return $this->base;
	}
	
	public static function secu($string) {
		//$connection = self::Connect();
		//return $connection->quote($string);
		return addslashes($string);
	}
	
	public static function Connect($baseType = null) {
		$base = array();
		
		$base['name'] = db_name;
		$base['hote'] = db_host;
		$base['user'] = db_user;
		$base['pass'] = db_pass;
		
		if(array_key_exists($base['name'], self::$_alreadyConnected)) {
			return self::$_alreadyConnected[$base['name']];
		}

		$newConnect = new BaseDeDonnee($base);
		return $newConnect;
	}
	
}
