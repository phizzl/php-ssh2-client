<?php

namespace Phizzl\Ssh2;


use Monolog\Logger;
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
     * @var Logger
     */
    private $logger;

    /**
     * SshClient constructor.
     * @param SshSession $sshSession
     */
    public function __construct(SshSession $sshSession)
    {
        $this->sshSession = $sshSession;
        $this->sftpSession = new SftpSession($this->sshSession);
        $this->logger = new Logger("SSH client");
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
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
    public function receiveFile($path, $localTarget)
    {
        $sftpUrl = $this->getSftpUrl($path);
        if(copy($sftpUrl, $localTarget) === false){
            throw new SftpException("Could not receive file \"{$path}\"");
        }

        return true;
    }

    /**
     * @param string $localSource
     * @param string $path
     * @return bool
     * @throws SftpException
     */
    public function sendFile($localSource, $path)
    {
        $sftpUrl = $this->getSftpUrl($path);
        $this->logger->debug("Sending file {$localSource} -> {$sftpUrl}");

        if(copy($localSource, $sftpUrl) === false){
            throw new SftpException("Could not send file \"{$localSource}\"");
        }

        return true;
    }

    /**
     * @param string $path
     * @return bool
     * @throws SftpException
     */
    public function removeFile($path)
    {
        $sftpUrl = $this->getSftpUrl($path);
        $this->logger->debug("Removing file {$sftpUrl}");

        if(unlink($sftpUrl) === false){
            throw new SftpException("Could not remove file \"{$path}\"");
        }

        return true;
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

    public function sendDirectory($localSource, $remote)
    {
        if(!$this->isDir($remote)){
            $this->createDirectory($remote);
        }

        $sftpUrl = $this->getSftpUrl($remote);
        foreach(new \DirectoryIterator($localSource) as $item){
            if($item->isDot()){
                continue;
            }

            if($item->isFile()){
                $this->logger->debug("Sending file {$item->getPathname()} -> {$sftpUrl}");
                if(copy($item->getPathname(), $sftpUrl . "/" . $item->getBasename()) === false){
                    throw new SftpException("Could not send file \"{$item->getPathname()}\"");
                }
            }
            else{
                $this->sendDirectory($item->getPathname(), $remote . "/" . $item->getBasename());
            }
        }

        return true;
    }

    /**
     * @param string $remote
     * @param string $localTarget
     * @return bool
     * @throws SftpException
     */
    public function receiveDirectory($remote, $localTarget)
    {
        if(!is_dir($localTarget)){
            mkdir($localTarget);
        }

        $sftpUrl = $this->getSftpUrl($remote);
        foreach(new SftpDirectoryIterator($sftpUrl) as $item){
            if($item->isDot()){
                continue;
            }

            if($item->isFile()){
                $this->logger->debug("Receive file {$item->getPathname()} -> {$localTarget}/{$item->getBasename()}");

                if(copy($item->getPathname(), $localTarget . "/" . $item->getBasename()) === false){
                    throw new SftpException("Could not receive file \"{$item->getPathname()}\"");
                }
            }
            else{
                $this->receiveDirectory($this->removeSftpBaseUrlFromString($item->getPathname()), $localTarget . "/" . $item->getBasename());
            }
        }

        return true;
    }

    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @return bool
     * @throws SftpException
     */
    public function createDirectory($path, $mode = 0777, $recursive = false)
    {
        $sftpUrl = $this->getSftpUrl($path);
        $this->logger->debug("Create directory {$sftpUrl}");

        if(mkdir($sftpUrl, $mode, $recursive) === false){
            throw new SftpException("Could not create directory \"{$path}\"");
        }

        return true;
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return bool
     * @throws SftpException
     */
    public function removeDirectory($path, $recursive = false)
    {
        $sftpUrl = $this->getSftpUrl($path);
        if($recursive) {
            foreach (new SftpDirectoryIterator($sftpUrl) as $item) {
                if ($item->isDot()) {
                    continue;
                }
                elseif ($item->isFile()) {
                    $this->logger->debug("Removing file {$item->getPathname()}");

                    if(unlink($item->getPathname()) === false) {
                        throw new SftpException("Could not remove file \"{$item->getPathname()}\"");
                    }
                }
                elseif ($item->isDir()) {
                    $this->removeDirectory($this->removeSftpBaseUrlFromString($item->getPathname()), true);
                }
            }
        }

        $this->logger->debug("Removing directory {$sftpUrl}");
        if(rmdir($sftpUrl) === false){
            throw new SftpException("Could not remove directory \"{$sftpUrl}\"");
        }

        return true;
    }
}