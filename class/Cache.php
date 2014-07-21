<?php

/**
 * Description of Cache
 *
 * @author sparks
 */

class Cache {
	
	public static $_infos = array();
	
	/*
	 * Création du cache
	 * -------------------------------------------------------------------------
	 */
	public static function initCache() {
		global $_SESSION;
		if(empty(self::$_infos)) {
			// Si les infos sont en cache, on les utilises
			if(!empty($_SESSION['cacheBDD'])) {
//				echo 'already in cache';
				self::$_infos = $_SESSION['cacheBDD'];
			}
			// Sinon on créer le cache
			else {
//				echo 'not in cache';
				
				// La base système est toujours présente
				$bases = array(db_name);
				
				foreach($bases as $base) {
					self::addBaseToCache($base);
				}
			}
		}
	}
	
	public static function addBaseToCache($base) {
		$thisBase = array($base => array());
		
		// On se connecte a la base de donnée (Retourne la connexion PDO)
		$conBase = BaseDeDonnee::Connect($base);
				
		// Preparation de la requète sortant les tables
		$requete = $conBase->getConnexion()->prepare('SHOW TABLES;');
		$requete->execute();

		// Si il y a des tables dans la base
		if($result = $requete->fetchAll(PDO::FETCH_ASSOC)) {
			// Bouclage des tables
			foreach($result as $r) {
				foreach($r as $osef => $table) {

					$thisTable = array();

					// Requète trouvant les colonnes
					$requete = $conBase->getConnexion()->prepare('SHOW COLUMNS FROM '.$table.';');
					$requete->execute();
					if($result = $requete->fetchAll(PDO::FETCH_ASSOC)) {
						$thisTable = $result;
					}
					
					$thisBase[$base][$table] = $thisTable;
				}
				
			}
		}
		
		self::$_infos = self::$_infos + $thisBase;
		$_SESSION['cacheBDD'] = self::$_infos;
	}
	
	public static function resetCache() {
		self::$_infos = array();
		$_SESSION['cacheBDD'] = array();
	}
	
	public static function refreshCache() {
		self::resetCache();
	}
	
	
	/*
	 * Retour d'informations
	 * -------------------------------------------------------------------------
	 */
	
	public static function baseExist($base) {
		self::initCache();
		if(array_key_exists($base, self::$_infos)) {
			return true;
		}
		return false;
	}
	
	public static function tableExist($table, $base = null) {
		self::initCache();
		if(!empty($base) && isset(self::$_infos[$base])) {
			if(array_key_exists($table, self::$_infos[$base])) {
				return true;
			}
		}
		else {
			if(!empty(self::$_infos)) {
				foreach(self::$_infos as $base) {
					if(array_key_exists($table, $base)) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	public static function getBase($table) {
		self::initCache();
		if(!empty(self::$_infos)) {
			foreach(self::$_infos as $name => $base) {
				if(array_key_exists($table, $base)) {
					return $name;
				}
			}
		}
		return null;
	}
	
	public static function getTables($base) {
		self::initCache();
		if(isset(self::$_infos[$base])) {
			$tables = array();
			foreach (self::$_infos[$base] as $key => $osef) {
				$tables[] = $key;
			}
			return $tables;
		}
	}
	
	public static function getColonnes($table) {
		self::initCache();
		foreach (self::$_infos as $bases) {
			foreach ($bases as $tables => $colonnes) {
				if($tables == $table) {
					$champs = array();
					foreach ($colonnes as $colonne) {
						$champs[] = $colonne['Field'];
					}
					return $champs;
				}
			}
		}
		return false;
	}
	
	public static function getPrimaryKey($table) {
		self::initCache();
		foreach (self::$_infos as $bases) {
			if(isset($bases[$table])) {
				foreach($bases[$table] as $colonne) {
					if($colonne['Key'] == 'PRI') {
						return $colonne['Field'];
					}
				}
			}
		}
		return false;
	}
}