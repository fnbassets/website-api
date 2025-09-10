<?php

namespace Fnbassets\WebsiteApi\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Fnbassets\WebsiteApi\Utils\Utils;

trait HttpTrait
{
    use Utils;

    protected $client;
    protected $params;

    public function getUserToken () {
        return $_COOKIE['USER_SESSION'] ?? null;
    }

    function getClient($token = false)
    {
        $headers = [
            'uloc-mi' => $this->apiClient,
            'X-AUTH-TOKEN' => $this->apiKey,
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'X_FORWARDED_FOR' => self::get_client_ip_env(),
        ];
        if ($token && $this->getUserToken()) {
            unset($headers['X-AUTH-TOKEN']);
            $headers['Authorization'] = 'Bearer ' . $this->getUserToken();
        }
        $params = [
            'timeout' => 100,
            'base_uri' => $this->apiUrl,
            'headers' => $headers,
            'verify' => false
        ];

        if (!isset($this->client) || $params !== $this->params) {
            $this->client = new Client($params);
            $this->params = $params;
        }

        return $this->client;
    }

    public function callAuthApi($method, $endpoint, $data = [], $userAuth = false)
    {
        return $this->callApi($method, $endpoint, $data, true);
    }

    public function callApi($method, $endpoint, $data = [], $userAuth = false)
    {
        try {
            $response = $this->getClient($userAuth)->request($method, $endpoint, $data);
        } catch (ClientException $e) {
            $this->requestError($e);
        } catch (\Throwable $exception) {
            throw $exception;
        }
        return json_decode($response->getBody(), true);
    }

    protected function requestError($e)
    {
        $body = json_decode($e->getResponse()->getBody(), true);
        if (isset($body['detail'])) {
            throw new \Exception('[api] ' . (is_array($body['detail']) ? serialize($body['detail']) : $body['detail']), $e->getResponse()->getStatusCode());
        }
        if (isset($body['error'])) {
            throw new \Exception('[api] ' . (is_array($body['message']) ? json_encode($body['message'], JSON_UNESCAPED_UNICODE) : $body['message']), $e->getResponse()->getStatusCode());
        }
        try {
            throw new \Exception('[api] ' . $body, $e->getResponse()->getStatusCode());
        } catch (\Throwable $exception) {
            throw new \Exception('[api] ' . $e->getResponse()->getBody(), $e->getResponse()->getStatusCode());
        }
    }
}