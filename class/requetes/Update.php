<?php

/**
 * Description of Update
 *
 * @author Sparks
 */
class Update extends Requete {
	
	public static $_version = '0.0.1';
	
	function __construct($table = null) {
		parent::__construct(2, $table);
	}
	
	function _executeType($db, $string, $marqueur) {
		
		$requete = $db->getConnexion()->prepare($string);
		
		if($requete->execute($marqueur)) {
			return true;
		}
		
		return false;
		
	}
	
	protected function _createRequete() {
		$requeteSTR = 'UPDATE ';
				
		$requeteSTR .= implode(', ', $this->tables);

		$requeteSTR .= ' SET ';

		$requeteSTR .= $this->_donnees();

		$requeteSTR .= $this->_conditions();
		
		return $requeteSTR;
	}
	
	
}
