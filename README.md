DnD-MagentoConnectorBundle
==========================

Magento Connector for Akeneo PIM

This connector allows you exporting data from the PIM to another server by a SFTP connection (FTP export is not already effective, see [roadmap](#roadmap))

You will need the following informations :

- Host
- Port
- Username
- Password

## Magento side : PIMGento extension is required
The part which read the files in Magento system works with PIMGento (a dedicateed Magento extension for Akeneo), not present on this repository (GoTo [PIMGento  Extension] for more infomations (https://github.com/Agence-DnD/PimGento ))

## Requirements

- php5
- php5-ssh2
- Akeneo PIM 1.3.x stable

## Installation instructions

Be sure that your server has SSH2 library installed (see [manual](http://php.net/manual/fr/ssh2.installation.php))

## Connector installation on Akeneo PIM

If it's not already done, install Akeneo PIM (see [this documentation](https://github.com/akeneo/pim-community-standard))

Get composer (with command line) :

    $ cd /my/pim/installation/dir
    $ curl -sS https://getcomposer.org/installer | php

Install DnD-MagentoConnectorBundle with composer :

In your composer.json, add the following code :

- In repositories :

    {
        "type": "vcs",
        "url": "http://github.com/Agence-DnD/DnD-MagentoConnectorBundle.git"
    }

- In require :

    "agencednd/magento-connector-bundle":"1.2"

Next, enter the following command line :

    $php composer.phar update

Enable the bundle in 'app/AppKernel.php' file, in the 'registerBundles' function, before the line 'return $bundles' :

    $bundles[] = new DnD\Bundle\MagentoConnectorBundle\DnDMagentoConnectorBundle();

## Configuration

Go to Spread > Export and then create your DnDMagentoConnectorBundle export type.

It is recommend to create exports with an explicit code, below an example of what you can enter :

companyname_environment_categories_export
companyname_environment_family_export
companyname_environment_attribute_export
companyname_environment_attribute_option_export
companyname_environment_product_export

Above, companyname match with the name of your company and environment is the environment on which you make your exports (devel, preprod, prod).

- Products export :

    - Channel choice
    - Last products modification date (if empty and the profil never been executed, all products are exported otherwise it export all products since the last profil execution datetime)
    - Export Product ID (number visible in the current url "/spread/export/ID")
    - Products status (enable / disable)
    - Products completness (completes / incompletes)
    - File path on Akeneo PIM server
    - Remote server host (public IP)
    - Remote server port (22 for SFTP connection)
    - Remote server username
    - Remote server password
    - Remote server file path (from the user root access)
    - CSV file delimiter
    - CSV file delimiter enclosure
    - CSV file with / without header
    - Remote server images file path (from the user root access)
    - Export images (yes / no), if you choose no, media columns will not be present in your CSV file and your images will not be transfered
    - Data to export (All data / All data without prices / Only prices)

Product export overview :
![products-export](http://img.dnd.fr/uploads/pim-screen1.png)

- Other exports :

    - File path on Akeneo PIM server
    - Remote server host (public IP)
    - Remote server port (22 for SFTP connection)
    - Remote server username
    - Remote server password
    - Remote server file path (from the user root access)
    - CSV file delimiter
    - CSV file delimiter enclosure
    - CSV file with / without header

Other exports overview :
![other-export](http://img.dnd.fr/uploads/pim-screen2.png)

## CRONJOB

To set up a cronjob which allow you to computerize exports (below an example for everyday at 4am) :

    $ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_categories_export --env=prod
    $ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_family_export --env=prod
    $ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_attribute_export --env=prod
    $ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_attribute_option_export --env=prod
    $ 0 4 * * * cd path/to/pim/; php app/console akeneo:batch:job companyname_environment_product_export --env=prod

## Roadmap

- Export files with FTP protocol
- Update password fields type (to hide their value)
- Export enabled and disabled products (actually enabled or disabled)
- Export complete and incomplete products (actually complete or incomplete)
- Remove ID product export and get it dynamically

## About us

Founded by lovers of innovation and design, [Agence Dn'D] (http://www.dnd.fr) assists companies for 11 years in the creation and development of customized digital (open source) solutions for web and E-commerce.
