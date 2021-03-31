# CakePHP-Sanitize
Plugin CakePHP qui permet de nettoyer les données renseignées. 

# Utiliser le behavior

Vous pouvez attacher le behavior à vos models via la variable $actAs.

```php
class User extends AppModel {

    public $actsAs = [
		'Sanitize' => [
			'fields' => [
				'nom' => 'stripHtml', 
				'prenom' => 'stripHtml', 
				'username' => 'stripHtml', 
			]
        ]
    ];

}
```

Cette exemple montre la façon dont les champs `nom`, `prenom`, `username` utiliseront la méthode `stripHtml` pour nettoyer leurs valeurs.

Voyons maintenant autre méthode pour charger le behavior à vos models.

```php
class User extends AppModel {

    public $actsAs = [
		'Sanitize' => [
			'fields' => '*',
			'exclude' => ['password', 'age'], 
			'map' => [
				'string' => 'stripHtml',
				'text' => 'stripHtml'
			]
        ]
    ];

}
```

La valeur `*` pour la clé `fields` permet de nettoyer l'ensemble des champs qui compose votre model. Vous pouvez facilement exclure certains champs du processus de nettoyage via la clé `exclude`. Dans notre exemple, les champs `password` et `age` ne seront pas nettoyés.
Vous pouvez définir les méthodes utilisées par type de champ. Dans l'exemple ci-dessus les champs de type `string` et `text` utiliseront la méthode `stripHtml` pour nettoyer le contenu. 

# Méthodes actuellement disponible
Voici la liste des méthodes actuelles du behavior : 
* `stripHtml` qui retire l'ensemble des balises HTML du contenu du champ
* `stripScript` qui retire les balises `<img>`, `<script>`, `<style>`, `<link>` du contenu du champ

# A venir
* Test unitaire
* Intégration de nouvelle méthode
