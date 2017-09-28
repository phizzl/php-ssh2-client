<?php


namespace Phizzl\Ssh2\Authentication;

class PasswordAuthentication implements AuthenticationInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * PasswordAuthentication constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param resource $session
     * @throws AuthenticationException
     */
    public function authenticate($session)
    {
         if(\ssh2_auth_password($session, $this->username, $this->password) === false){
            throw new AuthenticationException("Cannot authenticate with user \"{$this->username}\"");
         }
    }
}