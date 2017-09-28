<?php


namespace Phizzl\Ssh2\Authentication;

class NoneAuthentication implements AuthenticationInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * NoneAuthentication constructor.
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @param resource $session
     * @throws AuthenticationException
     */
    public function authenticate($session)
    {
         if(($result = \ssh2_auth_none($session, $this->username)) !== true){
            throw new AuthenticationException("Cannot authenticate. Available methods: " . implode(" ", $result));
         }
    }
}