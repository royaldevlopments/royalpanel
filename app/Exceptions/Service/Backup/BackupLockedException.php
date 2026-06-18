<?php

namespace RoyalPanel\Exceptions\Service\Backup;

use RoyalPanel\Exceptions\DisplayException;

class BackupLockedException extends DisplayException
{
    /**
     * TooManyBackupsException constructor.
     */
    public function __construct()
    {
        parent::__construct('Cannot delete a backup that is marked as locked.');
    }
}
