<?php
include 'chemin.php';

include_once _RACINE_.'config/config.php';
include_once _RACINE_.'config/database.php';

include_once _RACINE_.'fonctions/globals.fonction.php';

$requete = Requete::select('faq');
$retour = $requete->execute();

edebug($retour);