<?php

namespace Phizzl\Ssh2;


class SftpDirectoryIterator extends \DirectoryIterator
{
    /**
     * Rewind is not supported on streams
     */
    public function rewind()
    {

    }
}