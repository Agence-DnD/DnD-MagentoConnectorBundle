<?php

namespace DnD\Bundle\MagentoConnectorBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

/**
 *
 * @author    DnD Mimosa <mimosa@dnd.fr>
 * @copyright Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DnDMagentoConnectorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('readers.yml');
        $loader->load('processors.yml');
        $loader->load('writers.yml');
        
        $storageConfig = sprintf('storage_driver/%s.yml', $this->getStorageDriver($container));
        if (file_exists(__DIR__ . '/../Resources/config/' . $storageConfig)) {
            $loader->load($storageConfig);
        }
    }

    /**
     * Returns the storage driver used.
     *
     * @param ContainerBuilder $container
     *
     * @return string
     */
    protected function getStorageDriver(ContainerBuilder $container)
    {
        if (version_compare(Version::VERSION, '1.3.0', '<')) {
            return $container->getParameter('pim_catalog_storage_driver');
        } else {
            return $container->getParameter('pim_catalog_product_storage_driver');
    }
}
}
