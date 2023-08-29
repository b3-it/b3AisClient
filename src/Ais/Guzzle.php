<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

class GuzzleHttpClient {
    private $httpClient;

    public function __construct() {
        $this->httpClient = new Client();
    }

    public function sendHttpRequest($url) {
        $response = $this->httpClient->get($url);
        return $response->getBody()->getContents();
    }
}



