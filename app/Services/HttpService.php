<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

class HttpService
{
    protected $guzzle;
    protected $client;

    public function __construct(string $url)
    {
        $this->guzzle = new Client([
            'base_uri' => $url
        ]);
        $this->client = new Client();
    }

    public function getRequest($parameters = [])
    {
        return $this->request('?' . http_build_query($parameters))->getBody()->getContents();
    }

    public function postRequest(string $endpoint, $data = [], $headers = [])
    {
        return $this->request($endpoint, 'post', $data, $headers)->getBody()->getContents();
    }

    private function request(string $endpoint, $method = 'get', $customData = [], $customHeaders = [])
    {

        $headers = [
            'Content-Type'  => 'application/json'
        ];

        // Set custom headers
        if (!empty($customHeaders) && is_array($customHeaders)) {
            $headers = array_merge($headers, $customHeaders);
        }

        $data = [];

        // Set custom parameters
        if (!empty($customData) && is_array($customData)) {
            $data = array_merge($data, $customData);
        }

        // Define options for post request method...
        if (count($data)) {
            $options = [
                'headers' => $headers,
                RequestOptions::JSON => $data
            ];
        } // ...or define options for all other request methods
        else {
            $options = [
                'headers' => $headers,
            ];
        }

        return $this->guzzle->request($method, $endpoint, $options);
    }
}
