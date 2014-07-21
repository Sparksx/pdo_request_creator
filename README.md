PDO_request_creator
===================

Php class to create secure sql request   

Toutes le requetes se construises de façon statique. (Requete::...)     
Il existe un mode debug pour toutes les requetes, mais il est encore un peu bancale ; J'y travail.    


Dans la classe Requete, il y a 2 gros racourci interessant :    

###Le select_all   
```php
$resultat = Requete::select_all('articles', NULL, 'tarif_ht', 5);   
// SELECT * FROM articles WHERE `tarif_ht` = :marqueur0;    
// Cette ligne créer une requete permettant de recupérer toutes les lignes de la table article dont la colonne 'tarif_ht' est égale à 5
```

```php
$resultat = Requete::select_all('articles', NULL, array(    
	'tarif_ht' => 10,    
	'description' => 'bleu'    
));   
// SELECT * FROM articles WHERE `tarif_ht` = :marqueur0 AND `description` = :marqueur1;    
// Cette ligne créer une requete permettant de recupérer toutes les lignes de la table article dont la colonne 'tarif_ht' est égale a 10 ET la valeur de la colonne 'description' est bleu     
// NOTE : Toutes les conditions seront assamblés avec l'opérateur AND     
     
// select_all retourne FALSE si aucune ligne ne correspond, ou un tableau contenant la ou les lignes qui correspondent
```

il y a donc 2 façons d'utiliser la même fonction    
#####Pour debuger :    
```php
$resultat = Requete::select_all(table, donnees, ligne, valeur, false, true);
```
false (5ème paramètre) : récupère toutes les lignes (a true, seul la première ligne est récupéré)    
true (6ème paramètre) : true = mode debug activé    

###Le select_one

```php
$resultat = Requete::select_one('articles', NULL, 'tarif_ht', 5);    
// SELECT * FROM articles WHERE `tarif_ht` = :marqueur0;
// Cette ligne créer une requete permettant de recupérer la première ligne de la table article dont la colonne 'tarif_ht' est égale à 5
```

Le select_one s'utilise de la même façon que le select_all   
(Les conditions multiples sont aussi accessibles)    
Le debug du select_one est le 5ème paramètre (true/false)    

===================

Dans le cas ou ces selecteurs basique ne sufisent pas, il est possible de créer une requete de type select :   

$requeteTypeSelect = Requete::select();
// Le constructeur possède un paramètre facultatif : la ou les tables visé.
$requeteTypeSelect = Requete::select('articles');
$requeteTypeSelect = Requete::select(array('articles', 'articles_famille'));

// Il est également possible de selectionner la ou les tables au travers des fonctions table et tables
$requeteTypeSelect->table('panier_ligne');
$requeteTypeSelect->tables(array('panier_ligne', 'panier_entete'));

// A partir de l'objet créé, on peux définir quels données on souhaite récuperer :
$requeteTypeSelect->donnee('tarif_ht');
$requeteTypeSelect->donnees(array('designation', 'nom', 'article_famille.id_famille'));
// Si vous n'utilisez pas la fonction donnee(s), toutes les colonnes seront alors récupéré (SELECT * ...)
// Si vous demandez à récupérer une colonne qui n'existe pas (en dehors d'une fonction), celle ci seras ignoré
$requeteTypeSelect->donnee('COUNT(id_famille) AS nombreDeFamille');
// Les fonctions sont utilisables tels quels

// Il est possible de definir des conditions tels que :
$requeteTypeSelect->condition('AND', 'tarif_ht', '>', 20);
// Comme pour select_all il est possible de passer un tableau de condition : 
$requeteTypeSelect->conditions(array(
	'colonne' => 'valeur'
));
// Ou bien d'ecrire manuellement une condition (complexe ou pas)
$requeteTypeSelect->conditionString('id_article IN(30, 45, 56, 54)');
$requeteTypeSelect->conditionString('OR designation LIKE "%biere%"');
// Attention a sécuriser les variables passé dans ces conditions avec addslashes

// Il est possible de definir l'ordre de tri (ORDER BY)
$requeteTypeSelect->orderBy('tarif_ht', 'DESC', true);
// L'ordre par defaut est ASC
// Le dernier paramètre permet de dire si il faut verifier ou non l'existance de la colonne
// Il est utile dans le cas ou l'on utilise une fonction (COUNT(id_famille) AS nombreDeFamille) et que l'on veux trier en fonction du resultat (nombreDeFamille)

// La limit est egalement utilisable :
$requeteTypeSelect->limite(0, 10);
// Ne récupèrera que les 10 premières lignes

// Il est aussi possible de grouper les données :
$requeteTypeSelect->groupBy('id_famille');

//-------------------------------------------------
// Des exemples :
$selectArticles = Requete::select('articles');
$selectArticles
	->condition('OR', 'designation', 'LIKE', '%'._RECHERCHE_.'%')
	->condition('OR', 'description', 'LIKE', '%'._RECHERCHE_.'%')
	->order(array('colonne' => 'tarif_ht', 'ordre' => 'ASC'));

$selectArticles->debug(); // Permet de debuger la requete

$articles = $selectArticles->execute();
// Selectionne tout les données des articles dont la designation ou la description contient la constante _RECHERCHE_ et tri les resultats par tarif_ht croissant

//-------------------------------------------------

$supprimeLignesPanier = Requete::remove('panier_ligne');
$supprimeLignesPanier->condition('AND', 'id_panier', '=', $idPanierClient);
$supprimeLignesPanier->execute();
// Supprime toutes les lignes ou 'id_panier' est égale a $idPanierClient dans la tables panier_ligne
// Pour les suppression, seul les conditions sont utilisés

//-------------------------------------------------

$creerNouveauPanier = Requete::insert('panier_ligne');
$creerNouveauPanier->donnees(array(
	'id_panier' => $idPanierClient,
	'id_article' => $id_article,
	'quantite' => $quantite
));
$creerNouveauPanier->execute();
// Création d'une nouvelle ligne dans le panier, avec les valeurs passé dans les variables
// Pour les insertion, seul les données sont utilisés

//-------------------------------------------------

$modifierLignePanier = Requete::update('panier_ligne');
$modifierLignePanier->donnees(array(
		'quantite' => 10
	))
	->condition('AND', 'id_article', '=', $id_article)
	->condition('AND', 'id_panier', '=', $idPanierClient);

$modifierLignePanier->execute();
// Modifie la quantité des lignes du panier lorsque l'id_panier et l'id_article sont égales à ceux passés
// Pour les modifications, seuls les données et les conditions sont utilisé
