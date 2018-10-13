<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class LoggingService
{
    protected $logger;

    function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log a custom entry
     * @param $access
     */
    public function logAccess($access)
    {
        //no message because all data is passed through $context param
        $this->logger->info('', $access);
    }

    /**
     * Log a custom entry
     * @param $access
     */
    public function logError($access)
    {
        //no message because all data is passed through $context param
        $this->logger->error('', $access);
    }


}