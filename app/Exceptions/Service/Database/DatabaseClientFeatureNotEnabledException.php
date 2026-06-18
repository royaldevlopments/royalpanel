<?php

namespace RoyalPanel\Exceptions\Service\Database;

use RoyalPanel\Exceptions\RoyalPanelException;

class DatabaseClientFeatureNotEnabledException extends RoyalPanelException
{
    public function __construct()
    {
        parent::__construct('Client database creation is not enabled in this Panel.');
    }
}
