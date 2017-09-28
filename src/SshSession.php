<?php

namespace Phizzl\Ssh2;


use Phizzl\Ssh2\Authentication\AuthenticationInterface;

class SshSession
{
    /**
     * @var resource
     */
    private $session;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * SshSession constructor.
     * @param string $host
     * @param int $port
     * @param AuthenticationInterface $authentication
     */
    public function __construct($host, $port, AuthenticationInterface $authentication)
    {
        $this->host = $host;
        $this->port = $port;
        $this->authentication = $authentication;
    }

    /**
     * @return resource
     */
    public function getSession()
    {
        if($this->session === null) {
            $this->session = $this->createSession();
        }

        return $this->session;
    }

    /**
     * @return resource
     * @throws ConnectionException
     */
    private function createSession()
    {
        if(($session = \ssh2_connect($this->host, $this->port)) === false){
            throw new ConnectionException("Could not connect to host \"{$this->host}:{$this->port}\"");
        }

        $this->authentication->authenticate($session);

        return $session;
    }
}