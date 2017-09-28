<?php

use Phizzl\Ssh2\Authentication\PasswordAuthentication;
use Phizzl\Ssh2\SshClient;
use Phizzl\Ssh2\SshSession;

require 'vendor/autoload.php';

$sshSession = new SshSession("localhost", 22, new PasswordAuthentication("vagrant", "vagrant"));
$sshClient = new SshClient($sshSession);

var_dump($sshClient->listDirectory('~'));