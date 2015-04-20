<?php

namespace DnD\Bundle\MagentoConnectorBundle\Helper;

/**
 * Helper to ease SFTP connection management
 *
 * @author    DnD Mimosa <mimosa@dnd.fr>
 * @copyright 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SFTPConnection
{
    /**
     * @var handle
     */
    protected $connection;

    /**
     * @var handle
     */
    protected $sftp;

    /**
     * @param string $host
     * @param int    $port
     */
    public function __construct($host, $port = 22)
    {
        $this->connection = @ssh2_connect($host, $port);
        if (! $this->connection) {
            throw new \Exception("Could not connect to $host on port $port.");
        }
    }

    /**
     * Open a SFTP connection with credentials
     *
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        if (!@ssh2_auth_password($this->connection, $username, $password)) {
            throw new \Exception(
                "Could not authenticate with username $username " .
                "and password $password."
            );
        }

        $this->sftp = @ssh2_sftp($this->connection);

        if (!$this->sftp) {
            throw new \Exception("Could not initialize SFTP subsystem.");
        }
    }

    /**
     * Upload file to the remote target
     *
     * @param string $localFile
     * @param string $remoteFile
     */
    public function uploadFile($localFile, $remoteFile)
    {
        $sftp = $this->sftp;

        $stream = @fopen("ssh2.sftp://". $sftp . $remoteFile, 'w');

        if (!$stream) {
            throw new \Exception("Could not open remote file for writing: $remoteFile");
        }

        $fileContent = @file_get_contents($localFile);

        if ($fileContent === false) {
            throw new \Exception("Could not open local file for reading: $localFile.");
        }

        if (@fwrite($stream, $fileContent) === false) {
            throw new \Exception("Could not send data from local file $localFile to remote file $remoteFile.");
        }

        @fclose($stream);
    }

    /**
     * Create a remote directory on the SFTP connection
     * if it does not exist already
     *
     * @param string $path
     */
    public function createDirectory($path)
    {
        $sftp = $this->sftp;

        if (!is_dir("ssh2.sftp://". $sftp . $path)) {
            $stream = @mkdir("ssh2.sftp://". $sftp . $path, 0777, true);
            if (!$stream) {
                throw new \Exception("Could not create directory: $path");
            }
        }
    }
}
