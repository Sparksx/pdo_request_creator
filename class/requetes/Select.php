<?php

/**
 * Description of Select
 *
 * @author Sparks
 * @version 0.0.1
 */
class Select extends Requete {
	
	public static $_version = '0.0.1';
	
	function __construct($table = null) {
		parent::__construct(1, $table);
	}
	
	function getOne() {
		$this->getOne = true;
		return $this;
	}
	
	protected function _executeType($db, $string, $marqueur) {
		$retour = false;
		
		$requete = $db->getConnexion()->prepare($string);
		$requete->execute($marqueur);

		if($this->getOne) {
			$retour = $requete->fetch(PDO::FETCH_ASSOC);
		}
		else {
			$retour = $requete->fetchAll(PDO::FETCH_ASSOC);
		}
		
		$requete->closeCursor();
		
		return $retour;
		
	}
	
	protected function _createRequete() {
		$requeteSTR = 'SELECT ';
				
		$requeteSTR .= $this->_donnees();

		$requeteSTR .= ' FROM ';

		$requeteSTR .= implode(', ', $this->tables);

		$requeteSTR .= $this->_conditions();
		
		$requeteSTR .= $this->_group();

		$requeteSTR .= $this->_order();

		$requeteSTR .= $this->_limite();
		
		return $requeteSTR;
	}
}
