<?php

namespace SparkyApiClient;

use DateTime;
use DateTimeZone;
use Exception;
use stdClass;

/**
 * Sparky API Client PHP.
 *
 * @author Matthieu Roy <m@matthieuroy.be>
 * @license https://github.com/echo-five/sparky-api-client-php/blob/master/LICENSE MIT
 * @copyright 2023 Matthieu Roy
 * @link https://github.com/echo-five/sparky-api-client-php Documentation.
 */
class SparkyApiClient
{
    /**
     * The API host.
     *
     * @var string
     */
    private string $apiHost;
    
    /**
     * The API key (64 hexadecimal characters).
     *
     * @var string
     */
    private string $apiKey;
    
    /**
     * The API signature key (128 hexadecimal characters).
     *
     * @var string
     */
    private string $apiSignatureKey;
    
    /**
     * cURL transaction info.
     *
     * @var array
     */
    private array $curlInfo = [];
    
    /**
     * Received response body.
     *
     * @var string
     */
    private string $receivedBody;
    
    /**
     * Received response headers.
     *
     * @var string
     */
    private string $receivedHeaders = '';
    
    /**
     * Sent request headers.
     *
     * @var array
     */
    private array $sentHeaders = [];
    
    /**
     * Sent request payload.
     *
     * @var string
     */
    private string $sentPayload = '';
    
    /**
     * Sent request method.
     *
     * @var string
     */
    private string $sentMethod = '';
    
    /**
     * Sent request endpoint.
     *
     * @var string
     */
    private string $apiEndpoint = '';
    
    /**
     * Allow unsafe SSL certificates by disabling cURL SSL verify peer.
     *
     * @var bool
     */
    private bool $allowUnsafeCertificatesByDisablingCurlSslVerifyPeer = false;
    
    /**
     * The debug status.
     *
     * @var bool
     */
    private bool $debug = false;
    
    /**
     * The debug data.
     *
     * @var array
     */
    private array $debugData = [];
    
    /**
     * Constructor.
     *
     * @param string $apiHost
     *  The API host URL.
     * @param string $apiKey
     *  The API key (64 hexadecimal characters).
     * @param string $apiSignatureKey
     *  The API signature key (128 hexadecimal characters, optional).
     *
     * @throws \Exception
     */
    public function __construct(string $apiHost, string $apiKey, string $apiSignatureKey = '')
    {
        // Checks if the URL of the API host is provided.
        if (empty($apiHost)) {
            // Throw an exception.
            throw new Exception('Please provide the API URL!');
        }
        // Validate API host format (must use HTTPS).
        if (! $this->isValidHostFormat($apiHost)) {
            // Throw an exception.
            throw new Exception('The API host must use HTTPS protocol!');
        }
        // Set the API host URL.
        $this->apiHost = $apiHost;
        // Checks if the API key is provided.
        if (empty($apiKey)) {
            // Throw an exception.
            throw new Exception('Please provide the API Key!');
        }
        // Validate API key format.
        if (! $this->isValidApiKeyFormat($apiKey)) {
            // Throw an exception.
            throw new Exception('The API key must be exactly 64 hexadecimal characters!');
        }
        // Set the API key.
        $this->apiKey = $apiKey;
        // Set the API signature key.
        $this->apiSignatureKey = $apiSignatureKey ?? '';
        // Validate signature key format if provided.
        if (! empty($this->apiSignatureKey) && ! $this->isValidSignatureKeyFormat($this->apiSignatureKey)) {
            // Throw an exception.
            throw new Exception('The API signature key must be exactly 128 hexadecimal characters!');
        }
        // Initialize API request response.
        // Using str_repeat to avoid unlisted PHPStorm JSON inspection warnings on empty string literals.
        $this->receivedBody = str_repeat('', 1);
    }
    
    /**
     * Accept unsafe SSL certificates by disabling cURL SSL verify peer (for development only).
     *
     * This method disables SSL certificate verification (CURLOPT_SSL_VERIFYPEER = false),
     * allowing the use of self-signed certificates in local development environments.
     *
     * WARNING: This should NEVER be used in production as it disables SSL verification
     * and makes the connection vulnerable to man-in-the-middle attacks.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     */
    public function acceptUnsafeCertificatesByDisablingCurlSslVerifyPeer(): SparkyApiClient
    {
        // Enable unsafe certificate acceptance.
        $this->allowUnsafeCertificatesByDisablingCurlSslVerifyPeer = true;
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Get the debug data.
     *
     * @return array
     *  The debug data containing request count, time, and trace, or empty array if not initialized.
     */
    public function debugGet(): array
    {
        // Return the debug data or empty array if not initialized.
        return $this->debugData ?? [];
    }
    
    /**
     * Reset the debug data.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     * @throws \Exception
     *  If debug initialization fails.
     */
    public function debugReset(): SparkyApiClient
    {
        // Reset the data.
        $this->debugData = [];
        // Start the debug.
        $this->debugStart();
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Enable the debugging mode.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     * @throws \Exception
     *  If debug initialization fails.
     */
    public function debugStart(): SparkyApiClient
    {
        // Enable.
        $this->debug = true;
        // Reset the debug data if needed.
        if (empty($this->debugData)) {
            // Initialize the debug data.
            $this->debugInitialize();
        }
        // Debug.
        if ($this->debug) {
            // Add debug start message.
            $this->debugAddMessage('Start debug.');
        }
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Disable the debugging mode.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     * @throws \Exception
     *  If adding debug message fails.
     */
    public function debugStop(): SparkyApiClient
    {
        // Add stop message if debugging is active.
        if ($this->debug) {
            // Add debug stop message.
            $this->debugAddMessage('Stop debug.');
        }
        // Disable debugging.
        $this->debug = false;
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Get cURL info.
     *
     * @return array
     *  The cURL info array, or empty array if no request has been executed.
     */
    public function getCurlInfo(): array
    {
        // Return the cURL info or empty array if not set.
        return $this->curlInfo ?? [];
    }
    
    /**
     * Get the response.
     *
     * @param bool $object
     *  If the response must be returned as object (default), instead of a raw string.
     *
     * @return string|object
     *  The decoded response as object if $object is true, or raw JSON string if false.
     */
    public function getResponse(bool $object = true): object|string
    {
        // Return the response as decoded object or raw JSON string.
        return ($object) ? json_decode($this->receivedBody) : $this->receivedBody;
    }
    
    /**
     * Get the response data.
     *
     * @return object
     *  The data property from the response, or empty object if not present.
     */
    public function getResponseData(): object
    {
        // Return the data property from the response or empty object.
        return $this->getResponse()->data ?? (object) [];
    }
    
    /**
     * Get the response messages.
     *
     * @return array
     *  The messages array from the response, or empty array if not present.
     */
    public function getResponseMessages(): array
    {
        // Return the messages array from the response or empty array.
        return $this->getResponse()->messages ?? [];
    }
    
    /**
     * Get the response status.
     *
     * @return int
     *  The HTTP status code as int, or 0 if not present.
     */
    public function getResponseStatus(): int
    {
        // Return the HTTP status code as int or 0.
        return (int) $this->getResponse()->status ?? 0;
    }
    
    /**
     * Get the response headers.
     *
     * @param bool $array
     *  If the headers must be returned as array (default), instead of raw string.
     *
     * @return array|string
     *  The parsed headers as associative array if $array is true, or raw headers string if false.
     */
    public function getResponseHeaders(bool $array = true): array|string
    {
        // Return as array or raw string.
        return ($array) ? $this->responseParseHeaders($this->receivedHeaders) : $this->receivedHeaders;
    }

    /**
     * Get the complete sent request.
     *
     * @param bool $object
     *  If the request must be returned as object (default), instead of raw string.
     *
     * @return object|string
     *  The request as object if $object is true, or raw string if false.
     */
    public function getSent(bool $object = true): object|string
    {
        // Return as object or raw string.
        if ($object) {
            // Build request object.
            return (object) [
                'method' => $this->sentMethod,
                'endpoint' => $this->apiEndpoint,
                'headers' => $this->getSentHeaders(),
                'payload' => $this->getSentPayload(),
            ];
        }
        // Build raw string representation.
        $lines = [];
        $lines[] = $this->sentMethod . ' ' . $this->apiEndpoint;
        $lines[] = '';
        $lines[] = 'Headers:';
        $lines[] = $this->getSentHeaders(false);
        $lines[] = '';
        $lines[] = 'Payload:';
        $lines[] = $this->sentPayload;
        // Return formatted request string.
        return implode("\n", $lines);
    }

    /**
     * Get the sent headers.
     *
     * @param bool $array
     *  If the headers must be returned as array (default), instead of raw string.
     *
     * @return array|string
     *  The headers as array if $array is true, or raw string if false.
     */
    public function getSentHeaders(bool $array = true): array|string
    {
        // Return as array or raw string.
        if ($array) {
            // Parse headers array to associative array.
            $parsed = [];
            // For each header.
            foreach ($this->sentHeaders as $header) {
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $parsed[trim($parts[0])] = trim($parts[1]);
                }
            }
            // Sort by key alphabetically.
            ksort($parsed);
            // Return parsed headers as array.
            return $parsed;
        }
        // Return raw string with line breaks.
        return implode("\n", $this->sentHeaders);
    }

    /**
     * Get the sent payload.
     *
     * @param bool $object
     *  If the payload must be returned as object (default), instead of raw string.
     *
     * @return object|string
     *  The decoded payload as object if $object is true, or raw string if false.
     */
    public function getSentPayload(bool $object = true): object|string
    {
        // Return as object or raw string.
        if ($object) {
            // Try to decode JSON payload if not empty.
            if (! empty($this->sentPayload)) {
                $decoded = json_decode($this->sentPayload, false);
                // Convert array to object.
                if (is_array($decoded)) {
                    // Return converted object.
                    return (object) $decoded;
                }
                // Return decoded object or empty object if null.
                return $decoded !== null ? $decoded : new stdClass();
            }
            // Return empty object for empty payload.
            return new stdClass();
        }
        // Return raw payload string.
        return $this->sentPayload;
    }
    
    /**
     * Execute a request.
     *
     * @param string $requestType
     *  The request type (GET, POST, PUT, PATCH, DELETE).
     * @param string $requestEndpoint
     *  The request endpoint.
     * @param array $requestParams
     *  The request parameters (optional).
     *  For GET and DELETE: sent as query string parameters.
     *  For POST, PUT, PATCH: sent as request body.
     * @param string $requestMode
     *  The request mode.
     *  This is the mode used to send the request params.
     *  Allowed values: json (default), form, http.
     *  Only applicable for POST, PUT, PATCH requests.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     * @throws \Exception
     *  If cURL execution fails.
     */
    public function request(string $requestType, string $requestEndpoint, array $requestParams = [], string $requestMode = 'json'): SparkyApiClient
    {
        // Store request method and endpoint.
        $this->sentMethod = strtoupper(trim($requestType));
        $this->apiEndpoint = trim($requestEndpoint);
        // Initialize the request.
        $request = curl_init();
        // Initialize the request URL.
        $requestUrl = $this->requestGenerateUrl(trim($requestEndpoint));
        // Set the request headers.
        $requestHeaders = [
            'Authorization: Bearer ' . $this->apiKey,
        ];
        // Set the request signature.
        if (! empty($this->apiSignatureKey)) {
            // Generate nonce (32 random bytes encoded as hexadecimal) for replay protection.
            $requestNonce = bin2hex(random_bytes(32));
            // Generate timestamp in ISO 8601 format (UTC) for replay protection.
            $requestTimestamp = gmdate('Y-m-d\TH:i:s\Z');
            // Add timestamp and nonce headers (always sent for replay protection).
            $requestHeaders[] = "X-Api-Nonce: " . $requestNonce;
            $requestHeaders[] = "X-Api-Timestamp: " . $requestTimestamp;
            // Set the signature header (always includes timestamp and nonce).
            $requestHeaders[] = "X-Api-Signature: " . $this->requestGenerateSignature($requestType, $requestEndpoint, $requestParams, $requestNonce, $requestTimestamp);
        }
        // Switch on request type.
        switch (strtoupper(trim($requestType))) {
            // GET.
            case 'GET':
                // No payload for GET.
                $this->sentPayload = '';
                // Set the request URL with query parameters if provided.
                $requestUrl = (! empty($requestParams)) ? sprintf('%s?%s', $requestUrl, http_build_query($requestParams)) : $requestUrl;
                break;
            // DELETE.
            case 'DELETE':
                // No payload for DELETE.
                $this->sentPayload = '';
                // Set the DELETE method.
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'DELETE');
                // Set the request URL with query parameters if provided.
                $requestUrl = (! empty($requestParams)) ? sprintf('%s?%s', $requestUrl, http_build_query($requestParams)) : $requestUrl;
                break;
            // POST, PUT, PATCH.
            case 'POST':
            case 'PUT':
            case 'PATCH':
            default:
                // Set the request method.
                $method = strtoupper(trim($requestType));
                if ($method === 'PUT' || $method === 'PATCH') {
                    // Use custom request method for PUT and PATCH.
                    curl_setopt($request, CURLOPT_CUSTOMREQUEST, $method);
                }
                else {
                    // Force POST for POST or any other value (default behavior).
                    curl_setopt($request, CURLOPT_POST, 1);
                }
                // Switch on request mode.
                switch (strtoupper(trim($requestMode))) {
                    // FORM.
                    case 'FORM':
                        // Store payload as form data (cannot be converted to string easily).
                        $this->sentPayload = http_build_query($requestParams);
                        // Set the POST fields (cURL handles Content-Type with boundary automatically).
                        curl_setopt($request, CURLOPT_POSTFIELDS, $requestParams);
                        break;
                    // HTTP.
                    case 'HTTP':
                        // Store payload as URL-encoded string.
                        $this->sentPayload = http_build_query($requestParams);
                        // Set the POST fields as URL-encoded string.
                        curl_setopt($request, CURLOPT_POSTFIELDS, $this->sentPayload);
                        break;
                    // JSON.
                    case 'JSON':
                    default:
                        // Add the correct header.
                        $requestHeaders[] = 'Content-Type: application/json';
                        // Store payload as JSON string.
                        $this->sentPayload = json_encode($requestParams);
                        // Set the POST fields as JSON string.
                        curl_setopt($request, CURLOPT_POSTFIELDS, $this->sentPayload);
                        break;
                }
                break;
        }
        // Set the request URL.
        curl_setopt($request, CURLOPT_URL, $requestUrl);
        // Store the request headers.
        $this->sentHeaders = $requestHeaders;
        // Set the headers.
        curl_setopt($request, CURLOPT_HTTPHEADER, $requestHeaders);
        // Set remaining options.
        curl_setopt($request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($request, CURLOPT_TCP_FASTOPEN, true);
        // Enable header output in response.
        curl_setopt($request, CURLOPT_HEADER, true);
        // Allow unsafe SSL certificates if configured.
        if ($this->allowUnsafeCertificatesByDisablingCurlSslVerifyPeer) {
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        }
        // Execute the request.
        $requestResponse = curl_exec($request);
        // Set the request info attribute.
        $this->curlInfo = curl_getinfo($request);
        // Get header size from request info.
        $headerSize = $this->curlInfo['header_size'] ?? 0;
        // Separate headers and body.
        $this->receivedHeaders = substr($requestResponse, 0, $headerSize);
        $this->receivedBody = substr($requestResponse, $headerSize);
        // Check the request error.
        if (curl_errno($request)) {
            // Throw exception.
            throw new Exception(curl_error($request));
        }
        // Close the request.
        curl_close($request);
        // Debug.
        if (! empty($this->debug)) {
            // Time + Increment + Message.
            $this->debugAddRequestTime(($this->curlInfo['total_time'] ?? 0))->debugAddRequestCount()->debugAddMessage('Request | ' . $requestUrl);
        }
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Add debug message.
     *
     * @param string $message
     *  The trace $message.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     * @throws \Exception
     *  If DateTime creation fails.
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     */
    private function debugAddMessage(string $message): SparkyApiClient
    {
        // Set the timestamp in UTC.
        $timestamp = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s.u\Z');
        // Set the message.
        $this->debugData['requests']['trace'][$timestamp] = $message;
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Add debug request count.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     */
    private function debugAddRequestCount(): SparkyApiClient
    {
        // Increment.
        $this->debugData['requests']['count']++;
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Add debug request time.
     *
     * @param float $time
     *  The time to add in seconds.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     */
    private function debugAddRequestTime(float $time): SparkyApiClient
    {
        // Increment.
        $this->debugData['requests']['time'] = $this->debugData['requests']['time'] + $time;
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Initialize the debug data.
     *
     * @return SparkyApiClient
     *  The instance for method chaining.
     * @throws \Exception
     *  If initialization fails.
     * @noinspection PhpReturnValueOfMethodIsNeverUsedInspection
     */
    private function debugInitialize(): SparkyApiClient
    {
        // Initialize.
        $this->debugData = [
            'requests' => [
                'time' => 0,
                'count' => 0,
                'trace' => [],
            ],
        ];
        // Return the instance for method chaining.
        return $this;
    }
    
    /**
     * Get the request signature using cryptography.
     *
     * @param string $requestType
     *  The request type (GET, POST, etc.).
     * @param string $requestEndpoint
     *  The request endpoint.
     * @param array $requestParams
     *  The request parameters.
     * @param string $requestNonce
     *  The request nonce for replay protection.
     * @param string $requestTimestamp
     *  The request timestamp for replay protection.
     *
     * @return string
     *  The hexadecimal signature (128 characters).
     * @throws \Exception
     *  If signature key is invalid or signing fails.
     */
    private function requestGenerateSignature(string $requestType, string $requestEndpoint, array $requestParams, string $requestNonce, string $requestTimestamp): string
    {
        // Decode the private key from hexadecimal.
        $signatureKeyDecoded = hex2bin($this->apiSignatureKey);
        if ($signatureKeyDecoded === false || strlen($signatureKeyDecoded) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            // Throw an exception.
            throw new Exception('Invalid signature key format!');
        }
        // Build the message to sign based on HTTP method.
        $method = strtoupper(trim($requestType));
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            // Requests with body: sign the JSON body.
            // Canonicalize by sorting keys alphabetically (prevents bypass via key reordering).
            $sortedParams = $this->signatureCanonizeArray($requestParams);
            $message = json_encode($sortedParams, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        else {
            // GET/DELETE: sign the URI path + query string.
            $message = '/' . ltrim($requestEndpoint, '/');
            if (! empty($requestParams)) {
                // Canonicalize by sorting query parameters alphabetically.
                $sortedParams = $this->signatureCanonizeArray($requestParams);
                $message .= '?' . http_build_query($sortedParams);
            }
        }
        // Always append timestamp and nonce to the message for replay protection.
        // Format: message|nonce|timestamp
        $message .= '|' . $requestNonce . '|' . $requestTimestamp;
        // Sign the message using Sodium.
        try {
            $signature = sodium_crypto_sign_detached($message, $signatureKeyDecoded);
        }
        catch (Exception $e) {
            // Throw an exception.
            throw new Exception('Failed to sign the request: ' . $e->getMessage());
        }
        // Return the signature as hexadecimal.
        return bin2hex($signature);
    }
    
    /**
     * Get the request URL.
     *
     * @param string $requestEndpoint
     *  The request endpoint.
     *
     * @return string
     *  The complete request URL with host if configured.
     */
    private function requestGenerateUrl(string $requestEndpoint): string
    {
        // Initialize.
        $requestUrl = $requestEndpoint;
        // If the host is provided in class instantiation + request URL don't start by http(s)://
        if (! empty($this->apiHost) && ! preg_match('#^https?://#i', $requestUrl)) {
            // Add the host if the host is missing but provided in instantiation.
            $requestUrl = $this->apiHost . '/' . ltrim($requestUrl, '/');
        }
        // Return the complete URL.
        return $requestUrl;
    }
    
    /**
     * Sort an array recursively by keys.
     *
     * @param array $array
     *  The array to sort.
     *
     * @return array
     *  The sorted array with all nested arrays sorted alphabetically by key.
     */
    private function signatureCanonizeArray(array $array): array
    {
        // Sort the array by keys.
        ksort($array);
        // For each value.
        foreach ($array as $key => $value) {
            // If the value is an array.
            if (is_array($value)) {
                // Sort recursively.
                $array[$key] = $this->signatureCanonizeArray($value);
            }
        }
        // Return the sorted array.
        return $array;
    }
    
    /**
     * Validate API host format.
     *
     * @param string $host
     *  The API host to validate.
     *
     * @return bool
     *  True if the host uses HTTPS protocol, false otherwise.
     */
    private function isValidHostFormat(string $host): bool
    {
        // Host must start with https:// (HTTPS only).
        return str_starts_with($host, 'https://');
    }
    
    /**
     * Validate API key format.
     *
     * @param string $apiKey
     *  The API key to validate.
     *
     * @return bool
     *  True if the API key is exactly 64 hexadecimal characters, false otherwise.
     */
    private function isValidApiKeyFormat(string $apiKey): bool
    {
        // API key must be exactly 64 hexadecimal characters.
        return strlen($apiKey) === 64 && ctype_xdigit($apiKey);
    }
    
    /**
     * Validate signature key format.
     *
     * @param string $signatureKey
     *  The signature key to validate.
     *
     * @return bool
     *  True if the signature key is exactly 128 hexadecimal characters, false otherwise.
     */
    private function isValidSignatureKeyFormat(string $signatureKey): bool
    {
        // Signature key must be exactly 128 hexadecimal characters (64 bytes).
        return strlen($signatureKey) === 128 && ctype_xdigit($signatureKey);
    }
    
    /**
     * Parse response headers from raw string to associative array.
     *
     * @param string $headersRaw
     *  The raw headers string.
     *
     * @return array
     *  Associative array of headers (header name => header value).
     */
    private function responseParseHeaders(string $headersRaw): array
    {
        // Initialize headers array.
        $headers = [];
        // Split headers by line.
        $lines = explode("\r\n", trim($headersRaw));
        // For each line.
        foreach ($lines as $line) {
            // Skip empty lines and status line (HTTP/x.x).
            if (empty($line) || str_starts_with($line, 'HTTP/')) {
                continue;
            }
            // Split header name and value.
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                // Store header (trim whitespace).
                $headers[trim($parts[0])] = trim($parts[1]);
            }
        }
        // Sort by key alphabetically.
        ksort($headers);
        // Return parsed headers.
        return $headers;
    }
}
