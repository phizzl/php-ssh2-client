<?php

namespace Phizzl\Ssh2;


use Phizzl\Ssh2\Authentication\SftpSession;

class SshClient
{
    /**
     * @var SshSession
     */
    private $sshSession;

    /**
     * @var SftpSession
     */
    private $sftpSession;

    /**
     * SshClient constructor.
     * @param SshSession $sshSession
     */
    public function __construct(SshSession $sshSession)
    {
        $this->sshSession = $sshSession;
        $this->sftpSession = new SftpSession($this->sshSession);
    }

    /**
     * @return string
     */
    private function getSftpUrl()
    {
        $connectionId = intval($this->sftpSession->getSession());
        return "ssh2.sftp://{$connectionId}/";
    }

    /**
     * @param string $path
     * @return string
     */
    private function translatePath($path)
    {
        return substr($path, 0, 1) === '~'
            ? $this->getHomeDirectory() . substr($path, 2)
            : $path;
    }

    /**
     * @return string
     */
    public function getHomeDirectory()
    {
        return ssh2_sftp_realpath($this->sftpSession->getSession(), '.');
    }

    /**
     * @param string $path
     * @return array
     * @throws ConnectionException
     */
    public function listDirectory($path)
    {
        $listing = [];
        $path = $this->translatePath($path);

        if(($handle = opendir("{$this->getSftpUrl()}{$path}")) === false){
            throw new ConnectionException("Could not open dir \"{$path}\"");
        }

        while($entry = readdir($handle)){
            $listing[] = $entry;
        }

        return $listing;
    }
}