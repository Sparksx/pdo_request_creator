<?php

/**
 * Description of Insert
 *
 * @author Sparks
 */
class Insert extends Requete {
	
	function __construct($table = null) {
		parent::__construct(3, $table);
	}
	
	function _executeType($db, $string, $marqueur) {
		
		$requete = $db->getConnexion()->prepare($string);
	
		if($requete->execute($marqueur)) {
			return $db->getConnexion()->lastInsertId();
		}
		
		return false;
		
	}
	
	protected function _createRequete() {
		$requeteSTR = 'INSERT INTO ';
				
		$requeteSTR .= implode(', ', $this->tables);

		$requeteSTR .= ' SET ';

		$requeteSTR .= $this->_donnees();
		
		return $requeteSTR;
	}
	
	
	
}