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

    function getClient($token = false, $customToken = null)
    {
        $headers = [
            'uloc-mi' => $this->apiClient,
            'X-AUTH-TOKEN' => $this->apiKey,
            'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'X_FORWARDED_FOR' => self::get_client_ip_env(),
        ];

        $userToken = $customToken ?? $this->getUserToken();
        if ($token && $userToken) {
            unset($headers['X-AUTH-TOKEN']);
            $headers['Authorization'] = 'Bearer ' . $userToken;
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

    public function callApi($method, $endpoint, $data = [], $userAuth = false, $returnWithStatus = false)
    {
        try {
            $response = $this->getClient($userAuth)->request($method, $endpoint, $data);
            $body = json_decode($response->getBody(), true);

            if ($returnWithStatus) {
                return [
                    'data' => $body,
                    'statusCode' => $response->getStatusCode()
                ];
            }

            return $body;
        } catch (ClientException $e) {
            $this->requestError($e);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function callApiWithToken($method, $endpoint, $data = [], $token = null)
    {
        try {
            $headers = [
                'uloc-mi' => $this->apiClient,
                'User-Agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'X_FORWARDED_FOR' => self::get_client_ip_env(),
            ];

            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            } else {
                $headers['X-AUTH-TOKEN'] = $this->apiKey;
            }

            $client = new Client([
                'timeout' => 100,
                'base_uri' => $this->apiUrl,
                'headers' => $headers,
                'verify' => false
            ]);

            $response = $client->request($method, $endpoint, $data);
            return json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            $this->requestError($e);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    protected function requestError($e)
    {
        $body = json_decode($e->getResponse()->getBody(), true);
        $statusCode = $e->getResponse()->getStatusCode();

        if (isset($body['detail'])) {
            throw new \Exception('[api] ' . (is_array($body['detail']) ? serialize($body['detail']) : $body['detail']), $statusCode);
        }
        if (isset($body['error'])) {
            $message = $body['message'] ?? $body['error'];
            throw new \Exception('[api] ' . (is_array($message) ? json_encode($message, JSON_UNESCAPED_UNICODE) : $message), $statusCode);
        }
        try {
            throw new \Exception('[api] ' . $body, $statusCode);
        } catch (\Throwable $exception) {
            throw new \Exception('[api] ' . $e->getResponse()->getBody(), $statusCode);
        }
    }
}