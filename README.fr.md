DnD-MagentoConnectorBundle
==========================

Connecteur Magento pour le PIM Akeneo.

Ce connecteur vous permettra d'exporter vos données du PIM vers un autre serveur via une connexion SFTP (L'export par le biais du FTP n'est pas encore mis en place, voir la [feuille de route](#feuille-de-route)).

Vous aurez donc besoin des informations suivantes :
* Hôte
* Port
* Nom d'utilisateur
* Mot de passe

Attention : La partie qui interprète les fichiers côté Magento (ou autres solutions) n'est pas présente dans ce dépôt (voir le module [PimGento](https://github.com/Agence-DnD/PimGento)).

## Pré-requis

* php5
* php5-ssh2
* Akeneo PIM 1.2.x stable

## Instructions d'installation

Assurer vous que votre serveur possède [la bibliothèque ssh2](http://php.net/manual/fr/ssh2.installation.php).

## Installation du connecteur sur le PIM de Akeneo

Si ce n'est pas déjà fait, installer [le PIM de Akeneo](https://github.com/akeneo/pim-community-standard).

Récuperer composer (en ligne de commande) :
```console
$ cd /my/pim/installation/dir
$ curl -sS https://getcomposer.org/installer | php
```

Installer le DnD-MagentoConnectorBundle avec composer :

Dans votre ```composer.json```, ajouter le code suivant :

* Dans `repositories` :
```json
{
    "type": "vcs",
    "url": "http://github.com/Agence-DnD/DnD-MagentoConnectorBundle.git"
}
```

* Dans `require` :
```json
"agencednd/magento-connector-bundle":"1.2"
```

Entrer ensuite la ligne de commande suivante :
```console
$ php composer.phar update
```

Activer le bundle dans le fichier ```app/AppKernel.php```, dans la fonction ```registerBundles```, avant la ligne ```return $bundles``` :
```php
$bundles[] = new DnD\Bundle\MagentoConnectorBundle\DnDMagentoConnectorBundle();
```

## Configuration

Aller dans _Diffuser_ > _Profil d'export_ puis créer votre export de type _DnD Magento Connector Bundle_.

Il est recommandé de saisir un code clair et précis, voici un exemple de ce que vous pourriez mettre :
```
companyname_environment_categories_export
companyname_environment_family_export
companyname_environment_attribute_export
companyname_environment_attribute_option_export
companyname_environment_product_export
```

Ci-dessus, _companyname_ correspond au nom de votre société et environment à l'environnement sur lequel vous effectuez vos exports (devel, preprod, prod).

### Export des produits

* Choix du canal
* Choix de la dernière date de modification des produits (si cette dernière n'est pas entrée et que le profil n'a jamais été exécuté, tous les produits sont exportés sinon cela tous les produits modifiés depuis la dernière exécution du profil)
* ID Export Produit (ce dernier correspond au chiffre visible dans l'url sur laquelle vous vous trouvez "/spread/export/ID")
* Statut des produits (activés / désactivés)
* Complétude des produits (complets / incomplets)
* Chemin du fichier enregistré sur le serveur du PIM
* Hôte de votre serveur distant (IP publique)
* Port de votre serveur distant (22 pour une connexion SFTP)
* Utilisateur de votre serveur distant
* Mot de passe de l'utilisateur de votre serveur distant
* Chemin du fichier enregistré sur votre serveur distant (depuis la raçine accessible par votre utilisateur)
* Délimiteur du fichier CSV
* Caractère d'encadrement du fichier CSV
* Fichier CSV avec / sans en-tête
* Chemin des images enregistrées sur votre serveur distant (depuis la raçine accessible par votre utilisateur)
* Exporter les images (oui / non), si vous choisissez non, les colonnes des médias ne seront pas présentes dans le fichier CSV et les fichiers ne seront pas transférés
* Données à exporter (Toutes les données / Toutes les données sauf celles de type prix / Uniquement les données de type prix)

**Aperçu d'un export produit :**
![products-export](http://img.dnd.fr/uploads/pim-screen1.png)

### Autres exports

* Chemin du fichier enregistré sur le serveur du PIM
* Hôte de votre serveur distant (IP publique)
* Port de votre serveur distant (22 pour une connexion SFTP)
* Utilisateur de votre serveur distant
* Mot de passe de l'utilisateur de votre serveur distant
* Chemin du fichier enregistré sur votre serveur distant (depuis la raçine accessible par votre utilisateur)
* Délimiteur du fichier CSV
* Caractère d'encadrement du fichier CSV
* Fichier CSV avec / sans en-tête

**Aperçu d'un export :**
![other-export](http://img.dnd.fr/uploads/pim-screen2.png)

## Cron job

Pour mettre en place un cronjob qui permet d'automatiser les exports (ici un exemple pour tous les matins à 4 heures) :
```
$ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_categories_export --env=prod
$ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_family_export --env=prod
$ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_attribute_export --env=prod
$ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_attribute_option_export --env=prod
$ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_product_export --env=prod
```

## Feuille de route

* Implémentation de l'export des fichiers par le biais du protocole FTP
* Modification du type des champs mot de passe (afin qu'ils ne soient pas affichés)
* Modification de l'export des produits afin de pouvoir exporter les produits activés et désactivés
* Modification de l'export des produits afin de pouvoir exporter les produits complets et non complets
* Suppression du champ ID export produit afin qu'il soit récupéré dynamiquement
