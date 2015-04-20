<?php

namespace DnD\Bundle\MagentoConnectorBundle\Writer\File;

use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter as BaseCsvWriter;
use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use DnD\Bundle\MagentoConnectorBundle\Helper\SFTPConnection;

/**
 * Override of the Akeneo CSV write to send
 * CSV file to SFTP location
 *
 * @author    DnD Mimosa <mimosa@dnd.fr>
 * @copyright 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvWriter extends BaseCsvWriter
{
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
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
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
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getAllKeys(array $items)
    {
        $uniqueKeys = parent::getAllKeys($items);

        $identifier = array_shift($uniqueKeys);
        sort($uniqueKeys, SORT_FLAG_CASE | SORT_NATURAL);
        array_unshift($uniqueKeys, $identifier);

        return $uniqueKeys;
    }
}
