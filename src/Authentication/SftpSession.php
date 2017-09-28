<?php

namespace Phizzl\Ssh2\Authentication;


use Phizzl\Ssh2\SshSession;

class SftpSession
{
    /**
     * @var SshSession
     */
    private $sshSession;

    /**
     * @var resource
     */
    private $session;

    /**
     * SftpSession constructor.
     * @param SshSession $sshSession
     */
    public function __construct(SshSession $sshSession)
    {
        $this->sshSession = $sshSession;
    }

    /**
     * @return resource
     */
    public function getSession()
    {
        if($this->session === null){
            $this->session = ssh2_sftp($this->sshSession->getSession());
        }

        return $this->session;
    }
}