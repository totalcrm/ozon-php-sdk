<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service;

use ReflectionException;
use Throwable;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class AbstractService
 * @package TotalCRM\OzonApi\Service
 */
abstract class AbstractService
{
    /** @var array */
    private $config;

    /** @var ClientInterface */
    protected $client;

    /** @var RequestFactoryInterface */
    protected $requestFactory;

    public function __construct(array $config, ClientInterface $client, ?RequestFactoryInterface $requestFactory = null, ?StreamFactoryInterface $streamFactory = null)
    {
        $this->parseConfig($config);
        $this->client = $client;

        // request factory
        if (!$requestFactory && $this->client instanceof RequestFactoryInterface) {
            $requestFactory = $this->client;
        }
        assert(null !== $requestFactory);
        $this->requestFactory = $requestFactory;

        // stream factory
        if (!$streamFactory && $this->client instanceof StreamFactoryInterface) {
            $streamFactory = $this->client;
        }
        assert(null !== $streamFactory);
        $this->streamFactory = $streamFactory;
    }

    protected function getDefaultHost(): string
    {
        return 'https://api-seller.ozon.ru';
    }

    private function parseConfig(array $config): void
    {
        $keys = ['clientId', 'apiKey', 'host'];

        if (!$this->isAssoc($config)) {
            if (count($config) > 3) {
                throw new \LogicException('To many config parameters');
            }
            $config = array_combine($keys, array_pad($config, 3, null));
        }

        if (empty($config['clientId']) || empty($config['apiKey'])) {
            throw new \LogicException('Not defined mandatory config parameters `clientId` or `apiKey`');
        }

        if (!empty($config['host'])) {
            $url = parse_url($config['host']);
            $config['host'] = "{$url['scheme']}://{$url['host']}";
        } else {
            $config['host'] = rtrim($this->getDefaultHost(), '/');
        }

        $this->config = ArrayHelper::pick($config, $keys);
    }

    protected function createRequest(string $method, string $uri = '', $body = null): RequestInterface
    {
        if (is_array($body)) {
            $body = json_encode($body);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('json_encode error: '.json_last_error_msg());
            }
        }

        $request = $this->requestFactory
            ->createRequest($method, $this->config['host'].$uri)
            ->withHeader('Client-Id', $this->config['clientId'])
            ->withHeader('Api-Key', $this->config['apiKey'])
            ->withHeader('Content-Type', 'application/json');

        if ($body) {
            $request = $request->withBody($this->streamFactory->createStream($body));
        }

        return $request;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array|string|null $body
     * @param bool $parseResultAsJson
     * @param bool $returnOnlyResult
     * @return mixed
     * @throws OzonApiException
     * @throws Throwable
     */
    protected function request(string $method, string $uri = '', $body = null, bool $parseResultAsJson = true, bool $returnOnlyResult = true)
    {
        try {
            $request = $this->createRequest($method, $uri, $body);
            $response = $this->client->sendRequest($request);
            $responseBody = $response->getBody();

            // nyholm/psr7
            if ($response->getStatusCode() >= 400) {
                $this->throwOzonException($responseBody->getContents());
            }

            if (!$parseResultAsJson) {
                return $responseBody->getContents();
            }

            if ($responseBody->isSeekable() && 0 !== $responseBody->tell()) {
                $responseBody->rewind();
            }

            $arr = json_decode($responseBody->getContents(), true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('Invalid json response: '.$arr);
            }

            if (isset($arr['result']) && $returnOnlyResult) {
                return $arr['result'];
            }

            return $arr;
        } catch (RequestExceptionInterface $exc) {
            // guzzle
            $contents = $exc->getResponse()->getBody()->getContents();
            $this->throwOzonException($contents);
        }
    }

    /**
     * @param string $responseBodyContents
     * @throws OzonApiException
     * @throws Throwable
     * @throws ReflectionException
     */
    protected function throwOzonException(string $responseBodyContents): void
    {
        $errorData = json_decode($responseBodyContents, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new OzonApiException($responseBodyContents);
        }

        if (!isset($errorData['error']) || empty($errorData['error']['code'])) {
            throw new OzonApiException($errorData['message'] ?? 'Ozon error', (int) ($errorData['code'] ?? 0), $errorData['details'] ?? []);
        }

        if (!class_exists($className = $this->getExceptionClassByName($errorData['error']['code']))) {
            throw new OzonApiException($responseBodyContents);
        }

        $errorData = array_merge([
            'message' => '',
            'data'    => [],
        ], $errorData['error']);

        $refClass = new \ReflectionClass($className);
        /** @var Throwable $instance */
        $instance = $refClass->newInstance($errorData['message'], 0, $errorData['data'] ?? []);
        throw $instance;
    }

    /**
     * @param string $code
     * @return string
     */
    private function getExceptionClassByName(string $code): string
    {
        $parts = array_filter(explode('_', strtolower($code)));
        // 'error' будет заменен на Exception
        if ('error' === end($parts)) {
            unset($parts[key($parts)]);
        }
        $parts = array_map('ucfirst', $parts);
        $name = implode('', $parts);

        return "TotalCRM\\OzonApi\\Exception\\{$name}Exception";
    }

    /**
     * @param array $arr
     * @return array
     */
    protected function ensureCollection(array $arr): array
    {
        return $this->isAssoc($arr) ? [$arr] : $arr;
    }

    /**
     * @param array $arr
     * @return bool
     */
    protected function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
