<?php


namespace Phizzl\Ssh2\Authentication;

class PublicKeyAuthentication implements AuthenticationInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $pubKey;

    /**
     * @var string
     */
    private $privKey;

    /**
     * @var string
     */
    private $password;

    /**
     * PublicKeyAuthentication constructor.
     * @param string $username
     * @param string $pubKey
     * @param string $privKey
     * @param string $password
     */
    public function __construct($username, $pubKey, $privKey, $password = '')
    {
        $this->username = $username;
        $this->pubKey = $pubKey;
        $this->privKey = $privKey;
        $this->password = $password;
    }


    /**
     * @param resource $session
     * @throws AuthenticationException
     */
    public function authenticate($session)
    {
        if(\ssh2_auth_pubkey_file($session, $this->username, $this->pubKey, $this->privKey, $this->password) === false){
            throw new AuthenticationException("Cannot authenticate with key for user \"{$this->username}\"");
        }
    }
}