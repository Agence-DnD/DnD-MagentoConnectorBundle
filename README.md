DnD-MagentoConnectorBundle
==========================

Connecteur Magento pour le PIM Akeneo

Ce connecteur vous permettra d'exporter vos données du PIM vers un autre serveur via une connexion SFTP

Vous aurez donc besoin des informations suivantes :

- Hôte
- Port
- Nom d'utilisateur
- Mot de passe

Attention : la partie qui interprète les fichiers côté Magento (ou autres solutions) n'est pas présente sur ce repository

# Pré-requis

- php5
- php5-ssh2
- Akeneo PIM 1.2.x stable

# Instructions d'installation

Assurer vous que votre serveur possède la librairie ssh2 (voir http://php.net/manual/fr/ssh2.installation.php)

## Installation du connecteur sur le PIM de Akeneo

Si ce n'est pas déjà fait, installer le PIM de Akeneo (voir [cette documentation](https://github.com/akeneo/pim-community-standard))

Récuperer composer :

    $ cd /my/pim/installation/dir
    $ curl -sS https://getcomposer.org/installer | php

Installer le DnD-MagentoConnectorBundle avec composer :

Dans votre composer.json, ajouter le code suivant :

- Dans repositories :

    {
        "type": "vcs",
        "url": "http://github.com/Agence-DnD/DnD-MagentoConnectorBundle.git"
    }

- Dans require :

    "agencednd/magento-connector-bundle":"1.1"

Activer le bundle dans le fichier 'app/AppKernel.php', dans la fonction 'registerBundles', avant la ligne 'return $bundles' :

    $bundles[] = new DnD\MagentoConnectorBundle\DnDMagentoConnectorBundle();

# Configuration

Aller dans Diffuser > Profil d'export puis créer votre export de type DnD Magento Connector Bundle