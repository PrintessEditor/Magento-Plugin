<?php 

namespace Printess\PrintessDesigner\Api;

use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;
use Psr\Http\Message\ResponseInterface;
use Printess\PrintessDesigner\Api\Exceptions\ApiException;

class PrintessRepository
{
    /**
     * Default response timeout (in seconds).
     */
    public const DEFAULT_TIMEOUT = 10;

    /**
     * Default connect timeout (in seconds).
     */
    public const DEFAULT_CONNECT_TIMEOUT = 2;

    /**
     * HTTP status code for an empty ok response.
     */
    public const HTTP_NO_CONTENT = 204;

    private ClientInterface $_httpClient;
    private string $_serviceToken;
    private string $_apiUrl;

    public function __construct(string $_serviceToken, string $apiUrl = "https://api.printess.com")
    {
        $this->_serviceToken = $_serviceToken;
        $this->_apiUrl = $apiUrl;

        // $retryMiddlewareFactory = new Guzzle6And7RetryMiddlewareFactory;
        // $handlerStack = HandlerStack::create();
        // $handlerStack->push($retryMiddlewareFactory->retry());

        $client = new Client([
            GuzzleRequestOptions::VERIFY => CaBundle::getBundledCaBundlePath(),
            GuzzleRequestOptions::TIMEOUT => self::DEFAULT_TIMEOUT,
            GuzzleRequestOptions::CONNECT_TIMEOUT => self::DEFAULT_CONNECT_TIMEOUT]);//            'handler' => $handlerStack

        $this->_httpClient = $client;
    }

    /**
     * Send a request to the specified Printess api url.
     *
     * @param $httpMethod
     * @param $url
     * @param $headers
     * @param $httpBody
     * @return stdClass|null
     * @throws ApiException
     */
    private function send($httpMethod, $url, $headers, $httpBody)
    {
        $request = new Request($httpMethod, $url, $headers, $httpBody);

        try {
            $response = $this->_httpClient->send($request, ['http_errors' => false]);
        } catch (GuzzleException $e) {
            // Not all Guzzle Exceptions implement hasResponse() / getResponse()
            if (method_exists($e, 'hasResponse') && method_exists($e, 'getResponse')) {
                if ($e->hasResponse()) {
                    throw ApiException::createFromResponse($e->getResponse(), $request);
                }
            }

            throw new ApiException($e->getMessage(), $e->getCode(), null, $request, null);
        }

        if (! $response) {
            throw new ApiException("Did not receive API response.", 0, null, $request);
        }

        return $this->parseResponseBody($response);
    }

    /**
     * Parse the PSR-7 Response body
     *
     * @param ResponseInterface $response
     * @return stdClass|null
     * @throws ApiException
     */
    private function parseResponseBody(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        if (empty($body)) {
            if ($response->getStatusCode() === self::HTTP_NO_CONTENT) {
                return null;
            }

            throw new ApiException("No response body found.");
        }

        $object = @json_decode($body, false);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Unable to decode Printess response: '$body'.");
        }

        if ($response->getStatusCode() >= 400) {
            throw ApiException::createFromResponse($response);
        }

        return $object;
    }

    private function sendApiRequest(string $subPath, $data)
    {
        $headers = [
            'Accept' => "application/json",
            'Authorization' => "Bearer $this->_serviceToken"
        ];

        if(isset($data))
        {
            $headers['Content-Type'] = "application/json";
        }

        $url = $this->_apiUrl;

        if(!str_ends_with($url, "/"))
        {
            $url = $url . "/";
        }

        if(str_starts_with($subPath, "/"))
        {
            $url = $url . substr($subPath, 1);
        }
        else
        {
            $url = $url . $subPath;
        }

        $body = isset($data) ? json_encode($data) : null;

        return $this->send(isset($data) ? "POST" : "GET", $url, $headers, $body);
    }

    public function createDropshippingAddress($dropShippingData): int
    {
        return $this->sendApiRequest("dropshipData/save", $dropShippingData);
    }

    public function produce($produceData, $dropshipProduceData = null)
    {
        $handler = "production/produce";
        $payload = json_decode(json_encode($produceData), true);

        if(isset($dropshipProduceData))
        {
            $handler = "/dropship/produce";
            $payload["dropship"] = $dropshipProduceData;
        }

        return $this->sendApiRequest($handler, $payload);
    }

    public function getProductionStatus($jobId)
    {
        $handler = "production/status/get";
        return $this->sendApiRequest($handler, array("jobId" => $jobId));
    }
}

?>