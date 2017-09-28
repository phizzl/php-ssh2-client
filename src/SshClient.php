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
     * @param string $path
     * @return string
     */
    private function translatePath($path)
    {
        return substr($path, 0, 1) === '~'
            ? $this->getHomeDirectory() . substr($path, 1)
            : $path;
    }

    /**
     * @return string
     */
    private function getSftpProtocol()
    {
        return "ssh2.sftp://";
    }

    /**
     * @return string
     */
    private function getSftpBaseUrl()
    {
        $connectionId = intval($this->sftpSession->getSession());
        return $this->getSftpProtocol() . "{$connectionId}";
    }

    /**
     * @param string $str
     * @return bool|string
     */
    private function removeSftpBaseUrlFromString($str)
    {
        return substr($str, 0, strlen($this->getSftpBaseUrl())) === $this->getSftpBaseUrl()
            ? substr($str, strlen($this->getSftpBaseUrl()))
            : $str;
    }

    /**
     * @return string
     */
    private function getSftpUrl($path = '')
    {
        return $this->getSftpBaseUrl() . $this->translatePath($path);
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
     * @throws SftpException
     */
    public function listDirectory($path)
    {
        $listing = [];

        $sftpUrl = $this->getSftpUrl($path);

        if(!$this->isDir($path)){
            throw new \LogicException("\"{$path} must be a directory\"");
        }

        foreach(new SftpDirectoryIterator($sftpUrl) as $item){
            $listing[] = $this->removeSftpBaseUrlFromString($item->getPathname());
        }

        return $listing;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isDir($path)
    {
        $sftpUrl = $this->getSftpUrl($path);
        return \is_dir($this->translatePath($sftpUrl));
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isFile($path)
    {
        $sftpUrl = $this->getSftpUrl($path);
        return \is_file($this->translatePath($sftpUrl));
    }

    /**
     * @param string $path
     * @return bool
     */
    public function fileExists($path)
    {
        $sftpUrl = $this->getSftpUrl($path);
        return file_exists($this->translatePath($sftpUrl));
    }

    /**
     * @param string $path
     * @param string $localTarget
     * @return bool
     * @throws SftpException
     */
    public function receive($path, $localTarget)
    {
        $sftpUrl = $this->getSftpUrl($path);
        if(copy($sftpUrl, $localTarget) === false){
            throw new SftpException("Could not receive \"{$path}\"");
        }

        return true;
    }

    /**
     * @param string $localSource
     * @param string $path
     * @return bool
     * @throws SftpException
     */
    public function send($localSource, $path)
    {
        $sftpUrl = $this->getSftpUrl($path);
        if(copy($localSource, $sftpUrl) === false){
            throw new SftpException("Could not send \"{$localSource}\"");
        }

        return true;
    }
}