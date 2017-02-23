<?php

namespace NewsFeedReader\Provider;


use NewsFeedReader\Client\ClientInterface;

abstract class BaseNewsFeedReaderProvider implements NewsFeedReaderProviderInterface
{
    /**
     * @var array
     */
    protected $credentials;

    /**
     * @var ClientInterface
     */
    protected $client;


    /**
     * BaseNewsFeedReaderProvider constructor
     *
     * @param $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @param array $credentials
     *
     * @return self
     */
    public function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

}