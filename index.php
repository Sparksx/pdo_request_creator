<?php
include 'chemin.php';

include_once _RACINE_.'config/config.php';
include_once _RACINE_.'config/database.php';

include_once _RACINE_.'fonctions/varSecure.php';
include_once _RACINE_.'fonctions/globals.fonction.php';

$requete = select();
$requete->table('faq');



edebug($requete);

$retour = $requete->execute();

edebug($retour);