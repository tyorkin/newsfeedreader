<?php

namespace NewsFeedReader\Client;


interface ClientInterface
{
    public function request($url, $method, $header, $params);
}