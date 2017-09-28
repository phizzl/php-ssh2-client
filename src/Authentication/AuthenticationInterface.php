<?php

namespace Phizzl\Ssh2\Authentication;

interface AuthenticationInterface
{
    /**
     * @param resource $session
     */
    public function authenticate($session);
}