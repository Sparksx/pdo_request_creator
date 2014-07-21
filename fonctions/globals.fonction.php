<?php

/*
 * Affiche de manière lisible le paramètre
 */
function edebug($var) {
	echo '<pre>'.print_r($var, true).'</pre>';
}

/*
 * 
 */
function __autoload($class) {
	
	$repertoires = array(
		_RACINE_.'class/',
		_RACINE_.'class/requetes/',
	);
	
	foreach ($repertoires as $repertoire) {
		// On gère la casse (Seul la première lettre en maj)
		if(file_exists($repertoire.ucfirst($class).'.php')) {
			include_once($repertoire.ucfirst($class).'.php');
			return;
		}
	}
}

function element($typeElement, $idElement = null) {
	if(class_exists($typeElement)) {
		return new $typeElement($idElement);
	}
	else {
		return new Instance($typeElement, $idElement);
	}
}