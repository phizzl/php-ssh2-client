<?php

use Phizzl\Ssh2\Authentication\PasswordAuthentication;
use Phizzl\Ssh2\SshClient;
use Phizzl\Ssh2\SshSession;

require 'vendor/autoload.php';

$sshSession = new SshSession("m20701.wwwsrv.eu", 22, new PasswordAuthentication("vagrant", "vagrant!"));
$sshClient = new SshClient($sshSession);

var_dump($sshClient->sendFile(__DIR__ . '/../.bashrc', '~/trololo.txt'));
var_dump($sshClient->receiveFile('~/trololo.txt', __DIR__.'/../gollum.php'));
var_dump($sshClient->removeFile('~/trololo.txt'));
var_dump($sshClient->createDirectory('~/gollum'));
var_dump($sshClient->removeDirectory('~/gollum', true));

var_dump($sshClient->sendDirectory(__DIR__ . '/../php-ssh', '~/php-ssh'));
var_dump($sshClient->removeDirectory('~/php-ssh', true));

var_dump($sshClient->receiveDirectory('~/_apps/phpservermon', __DIR__ . '/copy'));
