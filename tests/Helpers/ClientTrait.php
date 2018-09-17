<?php

namespace App\Tests\Helpers;

trait ClientTrait
{

    /**
     * Setup general http client for all requests
     */
    public static function setupClient()
    {
        return static::createClient([], [
            'CONTENT_TYPE' => 'application/json'
        ]);
    }

    /**
     * Get the Entity Manager
     * @return mixed
     */
    public static function getEntityManager()
    {
        return self::setupClient()->getContainer()->get('doctrine')->getManager('default');
    }

}