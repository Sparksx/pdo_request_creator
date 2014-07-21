<?php

/**
 * Description of Delete
 *
 * @author Sparks
 */
class Delete extends Requete {
	
	function __construct($table = null) {
		parent::__construct(4, $table);
	}
	
	function _executeType($db, $string, $marqueur) {
		
		$requete = $db->getConnexion()->prepare($string);
		
		if($requete->execute($marqueur)) {
			return true;
		}
		
		return false;
		
	}
	
	protected function _createRequete() {
		$requeteSTR = 'DELETE FROM ';
				
		$requeteSTR .= implode(', ', $this->tables);

		$requeteSTR .= $this->_conditions();
		
		return $requeteSTR;
	}
	
	
	
}
