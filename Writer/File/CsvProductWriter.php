<?php

namespace DnD\Bundle\MagentoConnectorBundle\Writer\File;

use DnD\Bundle\MagentoConnectorBundle\Helper\SFTPConnection;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvProductWriter as BaseCsvProductWriter;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Specific Product Writer for Dnd Magento Module purpose
 *
 * @author    DnD Mimosa <mimosa@dnd.fr>
 * @copyright 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends BaseCsvProductWriter
{
    /**
     * @var string
     */
    protected $imageFolderPath;

    /**
     * @var boolean
     */
    protected $exportImages;

    /**
     * @var string
     */
    protected $exportPriceOnly;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

     /**
     * @var string
     */
    protected $remoteFilePath;

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Channel
     *
     * @var string $channel Channel code
     */
    protected $channel;

    /**
     * @var array
     */
    protected $fixedDatas = array("family", "groups", "categories", "RELATED-groups", "RELATED-products");

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ChannelManager               $channelManager
     * @param MediaManager                 $mediaManager
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ChannelManager $channelManager,
        MediaManager $mediaManager
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->channelManager      = $channelManager;
        $this->mediaManager        = $mediaManager;
    }

    /**
     * Set the configured channel
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the configured channel
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the host of the SFTP Connection
     *
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get the port of the SFTP Connection
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the port of the SFTP Connection
     *
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Get the port of the SFTP Connection
     *
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the username of the SFTP Connection
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get the username of the SFTP Connection
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the password of the SFTP Connection
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get the password of the SFTP Connection
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the remote file path
     *
     * @param string $remoteFilePath
     */
    public function setRemoteFilePath($remoteFilePath)
    {
	    $this->remoteFilePath = $remoteFilePath;
    }

     /**
     * Get the remote file path
     *
     * @return string
     */
    public function getRemoteFilePath()
    {
	    return $this->remoteFilePath;
    }

    /**
     * Set exportImages
     *
     * @param string $imageFolderPath imageFolderPath
     *
     * @return string
     */
    public function setImageFolderPath($imageFolderPath)
    {
	    $this->imageFolderPath = $imageFolderPath;
    }

    /**
     * get image Folder Path
     *
     * @return string imageFolderPath
     */
    public function getImageFolderPath()
    {
	    return $this->imageFolderPath;
    }

    /**
     * get exportImages
     *
     * @return string exportImages
     */
    public function getExportImages()
    {
        return $this->exportImages;
    }

    /**
     * Set exportImages
     *
     * @param string $exportImages exportImages
     *
     * @return AbstractProcessor
     */
    public function setExportImages($exportImages)
    {
        $this->exportImages = $exportImages;

        return $this;
    }

    /**
     * get exportPriceOnly
     *
     * @return string exportPriceOnly
     */
    public function getExportPriceOnly()
    {
        return $this->exportPriceOnly;
    }

    /**
     * Set exportPriceOnly
     *
     * @param string $exportPriceOnly exportPriceOnly
     *
     * @return AbstractProcessor
     */
    public function setExportPriceOnly($exportPriceOnly)
    {
        $this->exportPriceOnly = $exportPriceOnly;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        parent::flush();

        if (file_exists($this->getFilePath())) {
            $sftpConnection = new SFTPConnection($this->getHost(), $this->getPort());
            $sftpConnection->login($this->getUsername(), $this->getPassword());
            $sftpConnection->uploadFile($this->getFilePath(), $this->getRemoteFilePath());

            $this->stepExecution->addSummaryInfo(
                "dnd_magento_connector.export.remote_file_created",
                sprintf(
                    "sftp://%s:%s%s",
                    $this->getHost(),
                    $this->getPort(),
                    $this->getRemoteFilePath()
                )
            );
        } else {
            $this->stepExecution->addSummaryInfo("dnd_magento_connector.export.no_remote_file_created", "");
        }
    }

    /**
     * Override to copy media to sftp server.
     * 1.3 version
     *
     * {@inheritdoc}
     */
    protected function copyMedia(array $media)
    {
        $filePath = null;
        $exportPath = null;

        $filePath = $media['filePath'];
        $exportPath = $media['exportPath'];

        if (null === $filePath) {
            return;
        }

        $dirname = dirname($exportPath);

        $sftpConnection = new SFTPConnection($this->getHost(), $this->getPort());
        $sftpConnection->login($this->getUsername(), $this->getPassword());
        $sftpConnection->createDirectory($this->getImageFolderPath() . $dirname);
        $sftpConnection->uploadFile($filePath, $this->getImageFolderPath().$exportPath);
    }

    /**
     * @param array $media
     *
     * @return void
     */
    protected function copyMediaFromArray(array $media)
    {
        $this->copyMedia($media);
    }

    /**
     * Get only prices or all data without prices
     *
     * @param array $item
     *
     * @return array
     */
    protected function getProductPricesOnly(array $item)
    {
        if ($this->getExportPriceOnly() == 'all') {
            return $item;
        }
        $attributes      = $this->attributeRepository->getNonIdentifierAttributes();

        foreach ($attributes as $attribute) {
            if ($this->getExportPriceOnly() == 'onlyPrices') {
                if ($attribute->getBackendType() != 'prices') {
                    $attributesToRemove = preg_grep('/^' . $attribute->getCode() . 'D*/', array_keys($item));
                    foreach ($attributesToRemove as $attributeToRemove) {
                        unset($item[$attributeToRemove]);
                    }
                }
            } elseif ($this->getExportPriceOnly() == 'withoutPrices') {
                if ($attribute->getBackendType() == 'prices') {
                    $attributesToRemove = preg_grep('/^' . $attribute->getCode() . 'D*/', array_keys($item));
                    foreach ($attributesToRemove as $attributeToRemove) {
                        unset($item[$attributeToRemove]);
                    }
                }
            }
        }

        if ($this->getExportPriceOnly()  == 'onlyPrices') {
            foreach ($this->fixedDatas as $fixedData) {
                unset($item[$fixedData]);
            }
        }

        return $item;
    }

    /**
     * Add channel code to metric attributes header columns
     *
     * @param array $item
     *
     * @return array
     */
    protected function formatMetricsColumns($item)
    {
        $attributes      = $this->attributeRepository->getNonIdentifierAttributes();

        foreach ($attributes as $attribute) {
            if ('metric' === $attribute->getBackendType()) {
                if (array_key_exists($attribute->getCode(), $item)) {
                    $item[$attribute->getCode() . '-' . $this->getChannel()] = $item[$attribute->getCode()];
                    unset($item[$attribute->getCode()]);
                }
            }
        }

        return $item;
    }


    /**
     * Remove all column of attributes with type media
     *
     * @param array $item
     *
     * @return array
     */
    protected function removeMediaColumns($item)
    {
        $attributeEntity = $this->entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $mediaAttributesCodes = $attributeEntity->findMediaAttributeCodes();

        foreach ($mediaAttributesCodes as $mediaAttributesCode) {
            if (array_key_exists($mediaAttributesCode, $item)) {
                unset($item[$mediaAttributesCode]);
            }
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'channel' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'pim_base_connector.export.channel.label',
                        'help'     => 'pim_base_connector.export.channel.help'
                    ]
                ],
                'host' => [
                    'options' => [
                        'label'    => 'dnd_magento_connector.export.host.label',
                        'help'     => 'dnd_magento_connector.export.host.help',
                        'required' => true
                    ]
                ],
                'port' => [
                    'options' => [
                        'label'    => 'dnd_magento_connector.export.port.label',
                        'help'     => 'dnd_magento_connector.export.port.help',
                        'required' => true
                    ]
                ],
                'username' => [
                    'options' => [
                        'label'    => 'dnd_magento_connector.export.username.label',
                        'help'     => 'dnd_magento_connector.export.username.help',
                        'required' => true
                    ]
                ],
                'password' => [
                    'options' => [
                        'label'    => 'dnd_magento_connector.export.password.label',
                        'help'     => 'dnd_magento_connector.export.password.help',
                        'required' => true
                    ]
                ],
                'remoteFilePath' => [
                    'options' => [
                        'label'    => 'dnd_magento_connector.export.remoteFilePath.label',
                        'help'     => 'dnd_magento_connector.export.remoteFilePath.help',
                        'required' => true
                    ]
                ],
                'imageFolderPath' => [
                    'options' => [
                        'label'    => 'dnd_magento_connector.export.imageFolderPath.label',
                        'help'     => 'dnd_magento_connector.export.imageFolderPath.help',
                        'required' => false
                    ]
                ],
                'exportImages' => [
                    'type'    => 'switch',
                    'options' => [
                        'help'    => 'dnd_magento_connector.export.exportImages.help',
                        'label'   => 'dnd_magento_connector.export.exportImages.label',
                    ]
                ],
                'exportPriceOnly' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => [
                            'all' 			=> 'Export all',
                            'withoutPrices' => 'Export all without prices',
                            'onlyPrices'	=> 'Export only prices'
                        ],
                        'required' => true,
                        'select2'  => true,
                        'label'    => 'dnd_magento_connector.export.exportPriceOnly.label',
                        'help'     => 'dnd_magento_connector.export.exportPriceOnly.help'
                    ]
                ]
            ]
        );
    }
}
