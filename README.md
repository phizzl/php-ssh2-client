SSH2 client
===========

A SSH2 client written in PHP

## Authentication
You may choose between the authentication modes pubkey, password and none.

### Authentication with password
```php
$auth = new PasswordAuthentication("vagrant", "vagrant");
```

### Authentication with public and private keypair
```php
$auth = new PublicKeyAuthentication("vagrant", "/path/to/id_rsa.pub", "/path/to/id_rsa", "keypassword");
```

### No authentication
```php
$auth = new NoneAuthentication("vagrant");
```


## Creating the SSH2 session
After you configured your authentication you may create the SSH2 session and pass the authentication to it.

```php
$auth = new PasswordAuthentication("vagrant", "vagrant");
$session = new SshSession("localhost", 22, $auth);
```

## Creating the SSH2 client
Now that you have a session you can create the SSH2 client and pass the session to it.

```php
$auth = new PasswordAuthentication("vagrant", "vagrant");
$session = new SshSession("localhost", 22, $auth);
$sshClient = new SshClient($session);
```

## Using the SSH2 client
After you created the SSH2 client you are ready to use it.

```php
$sshClient->sendFile('/local/path/to/file.txt, '~/uploads/file.txt');
```

```php
$sshClient->receiveFile('~/downloads/remote.file', '/local/path/local.file');
```

```php
$sshClient->removeFile('~/downloads/remote.file');
```

```php
$sshClient->createDirectory('~/uploads/newdir');
```

```php
$sshClient->removeDirectory('~/uploads/newdir', true)
```

```php
$sshClient->sendDirectory('/local/dir', '~/uploads/newdir');
```

```php
$sshClient->removeDirectory('~/uploads/newdir', true);
```

```php
$sshClient->receiveDirectory('~/downloads/backup', '/local/dir/backup');
```