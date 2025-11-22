<?php

/**
 * Comprehensive API Test Suite.
 *
 * This test file simulates a user who has:
 * - The API host URL.
 * - The API key.
 * - The API signature key.
 * - The OpenAPI specification.
 *
 * Purpose: Verify that the library works correctly and the documentation is accurate.
 */

// Load Composer autoloader.
require __DIR__ . '/../vendor/autoload.php';

// Use declaration.
use SparkyApiClient\SparkyApiClient;

// Configuration from the provided information.
const API_HOST = 'https://local.sparky.matthieuroy.be';
const API_KEY = '449e87277b2d59e2ca34493b26b1bb26cc9ebbb727429ebdea582098374dfee3';
const API_SIGNATURE_KEY = 'a25fb6f935296583beea009eef58d7ba8bd3dce93d550b5dc652374ecfd2fe54cd556550ca7b8fb35cd6d4af2f8a1dd4ac31283518de6467504375a7ce170216';

// Test results storage.
$testResults = [];
$testCounter = 0;

/**
 * Helper function to log test results.
 *
 * @param string $testName The name of the test.
 * @param bool $success Whether the test passed.
 * @param string $message Additional message.
 * @param mixed $data Additional data to display.
 *
 * @return void
 */
function logTest(string $testName, bool $success, string $message = '', mixed $data = null): void
{
    global $testResults, $testCounter;
    $testCounter++;

    $testResults[] = [
        'number' => $testCounter,
        'name' => $testName,
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ];

    $status = $success ? '✓' : '✗';
    $color = $success ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";

    echo "{$color}{$status}{$reset} {$testName}";
    if (!empty($message)) {
        echo " — {$message}";
    }
    echo "\n";
}

/**
 * Helper function to display section separator.
 *
 * @param string $title The section title.
 *
 * @return void
 */
function displaySection(string $title): void
{
    echo "\n\033[1m{$title}\033[0m\n";
}

// Start testing.
echo "\n\033[1mSPARKY API CLIENT PHP - TEST SUITE\033[0m\n";

// ==============================================================================
// SECTION 1: LIBRARY INITIALIZATION TESTS
// ==============================================================================
displaySection('SECTION 1: LIBRARY INITIALIZATION TESTS');

// Test 1.1: Initialize without signature key (simple requests).
try {
    $simpleClient = new SparkyApiClient(API_HOST, API_KEY);
    logTest(
        'Initialize without signature key',
        true,
        'Client initialized successfully for simple (unsigned) requests'
    );
} catch (Exception $e) {
    logTest(
        'Initialize without signature key',
        false,
        'Failed to initialize: ' . $e->getMessage()
    );
}

// Test 1.2: Initialize with signature key (signed requests).
try {
    $signedClient = new SparkyApiClient(API_HOST, API_KEY, API_SIGNATURE_KEY);
    // Accept unsafe certificates for local development.
    $signedClient->acceptUnsafeCertificatesByDisablingCurlSslVerifyPeer();
    logTest(
        'Initialize with signature key',
        true,
        'Client initialized successfully for signed requests'
    );
} catch (Exception $e) {
    logTest(
        'Initialize with signature key',
        false,
        'Failed to initialize: ' . $e->getMessage()
    );
}

// Test 1.3: Initialize with invalid API key (should fail).
try {
    $invalidClient = new SparkyApiClient(API_HOST, 'invalid_key');
    logTest(
        'Initialize with invalid API key',
        false,
        'Should have thrown an exception but did not'
    );
} catch (Exception $e) {
    logTest(
        'Initialize with invalid API key',
        true,
        'Correctly rejected invalid API key: ' . $e->getMessage()
    );
}

// Test 1.4: Initialize with invalid signature key (should fail).
try {
    $invalidClient = new SparkyApiClient(API_HOST, API_KEY, 'invalid_signature_key');
    logTest(
        'Initialize with invalid signature key',
        false,
        'Should have thrown an exception but did not'
    );
} catch (Exception $e) {
    logTest(
        'Initialize with invalid signature key',
        true,
        'Correctly rejected invalid signature key: ' . $e->getMessage()
    );
}

// ==============================================================================
// SECTION 2: SIMPLE REQUEST TESTS (WITHOUT SIGNATURE)
// ==============================================================================
displaySection('SECTION 2: SIMPLE REQUEST TESTS (WITHOUT SIGNATURE)');

// Accept unsafe certificates for local development.
$simpleClient->acceptUnsafeCertificatesByDisablingCurlSslVerifyPeer();

// Test 2.1: POST /ping endpoint.
try {
    $simpleClient->request('post', '/api/v1/ping');
    $response = $simpleClient->getResponse();
    $status = $simpleClient->getResponseStatus();

    logTest(
        'POST /api/v1/ping (simple request)',
        $status === 200 && isset($response->data->status) && $response->data->status === 'pong',
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/ping (simple request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 2.2: POST /heartbeat endpoint.
try {
    $simpleClient->request('post', '/api/v1/heartbeat');
    $response = $simpleClient->getResponse();
    $status = $simpleClient->getResponseStatus();

    logTest(
        'POST /api/v1/heartbeat (simple request)',
        $status === 200 && isset($response->data->status) && $response->data->status === 'beating',
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/heartbeat (simple request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 2.3: POST /mirror endpoint with data.
try {
    $testData = [
        'key1' => 'value1',
        'key2' => 'value2',
        'nested' => [
            'foo' => 'bar',
        ],
    ];

    $simpleClient->request('post', '/api/v1/mirror', $testData);
    $response = $simpleClient->getResponse();
    $status = $simpleClient->getResponseStatus();
    $data = $simpleClient->getResponseData();

    logTest(
        'POST /api/v1/mirror with data (simple request)',
        $status === 200 && $data->key1 === 'value1' && $data->key2 === 'value2',
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/mirror with data (simple request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 2.4: POST /mirror endpoint without data.
try {
    $simpleClient->request('post', '/api/v1/mirror');
    $response = $simpleClient->getResponse();
    $status = $simpleClient->getResponseStatus();

    logTest(
        'POST /api/v1/mirror without data (simple request)',
        $status === 200,
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/mirror without data (simple request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 2.5: POST /version endpoint.
try {
    $simpleClient->request('post', '/api/v1/version');
    $response = $simpleClient->getResponse();
    $status = $simpleClient->getResponseStatus();

    logTest(
        'POST /api/v1/version (simple request)',
        $status === 200 && isset($response->data->version),
        "HTTP Status: {$status}, Version: " . ($response->data->version ?? 'N/A'),
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/version (simple request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 2.6: POST /versions endpoint.
try {
    $simpleClient->request('post', '/api/v1/versions');
    $response = $simpleClient->getResponse();
    $status = $simpleClient->getResponseStatus();

    logTest(
        'POST /api/v1/versions (simple request)',
        $status === 200 && isset($response->data->versions),
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/versions (simple request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// ==============================================================================
// SECTION 3: SIGNED REQUEST TESTS (WITH SIGNATURE)
// ==============================================================================
displaySection('SECTION 3: SIGNED REQUEST TESTS (WITH SIGNATURE)');

// Test 3.1: POST /ping endpoint (signed).
try {
    $signedClient->request('post', '/api/v1/ping');
    $response = $signedClient->getResponse();
    $status = $signedClient->getResponseStatus();

    logTest(
        'POST /api/v1/ping (signed request)',
        $status === 200 && isset($response->data->status) && $response->data->status === 'pong',
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/ping (signed request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 3.2: POST /heartbeat endpoint (signed).
try {
    $signedClient->request('post', '/api/v1/heartbeat');
    $response = $signedClient->getResponse();
    $status = $signedClient->getResponseStatus();

    logTest(
        'POST /api/v1/heartbeat (signed request)',
        $status === 200 && isset($response->data->status) && $response->data->status === 'beating',
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/heartbeat (signed request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 3.3: POST /mirror endpoint with data (signed).
try {
    $testData = [
        'key1' => 'value1',
        'key2' => 'value2',
        'nested' => [
            'foo' => 'bar',
        ],
    ];

    $signedClient->request('post', '/api/v1/mirror', $testData);
    $response = $signedClient->getResponse();
    $status = $signedClient->getResponseStatus();
    $data = $signedClient->getResponseData();

    logTest(
        'POST /api/v1/mirror with data (signed request)',
        $status === 200 && $data->key1 === 'value1' && $data->key2 === 'value2',
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/mirror with data (signed request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 3.4: POST /version endpoint (signed).
try {
    $signedClient->request('post', '/api/v1/version');
    $response = $signedClient->getResponse();
    $status = $signedClient->getResponseStatus();

    logTest(
        'POST /api/v1/version (signed request)',
        $status === 200 && isset($response->data->version),
        "HTTP Status: {$status}, Version: " . ($response->data->version ?? 'N/A'),
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/version (signed request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 3.5: POST /versions endpoint (signed).
try {
    $signedClient->request('post', '/api/v1/versions');
    $response = $signedClient->getResponse();
    $status = $signedClient->getResponseStatus();

    logTest(
        'POST /api/v1/versions (signed request)',
        $status === 200 && isset($response->data->versions),
        "HTTP Status: {$status}",
        $response
    );
} catch (Exception $e) {
    logTest(
        'POST /api/v1/versions (signed request)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// ==============================================================================
// SECTION 4: LIBRARY FEATURES TESTS
// ==============================================================================
displaySection('SECTION 4: LIBRARY FEATURES TESTS');

// Test 4.1: Method chaining.
try {
    $response = $simpleClient->request('post', '/api/v1/ping')->getResponse();

    logTest(
        'Method chaining (request -> getResponse)',
        isset($response->data->status) && $response->data->status === 'pong',
        'Method chaining works correctly'
    );
} catch (Exception $e) {
    logTest(
        'Method chaining (request -> getResponse)',
        false,
        'Method chaining failed: ' . $e->getMessage()
    );
}

// Test 4.2: Get response as raw JSON string.
try {
    $simpleClient->request('post', '/api/v1/ping');
    $responseRaw = $simpleClient->getResponse(false);
    $responseObj = $simpleClient->getResponse(true);

    logTest(
        'Get response as raw JSON vs object',
        is_string($responseRaw) && is_object($responseObj),
        "Raw is string: " . (is_string($responseRaw) ? 'Yes' : 'No') . ", Object is object: " . (is_object($responseObj) ? 'Yes' : 'No')
    );
} catch (Exception $e) {
    logTest(
        'Get response as raw JSON vs object',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.3: Get response status.
try {
    $simpleClient->request('post', '/api/v1/ping');
    $status = $simpleClient->getResponseStatus();

    logTest(
        'Get response status',
        $status === 200,
        "Status: {$status}"
    );
} catch (Exception $e) {
    logTest(
        'Get response status',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.4: Get response data.
try {
    $simpleClient->request('post', '/api/v1/ping');
    $data = $simpleClient->getResponseData();

    logTest(
        'Get response data',
        is_object($data) && isset($data->status) && $data->status === 'pong',
        'Data retrieved correctly'
    );
} catch (Exception $e) {
    logTest(
        'Get response data',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.5: Get response messages.
try {
    $testData = ['key1' => 'value1', 'key2' => 'value2'];
    $simpleClient->request('post', '/api/v1/mirror', $testData);
    $messages = $simpleClient->getResponseMessages();

    logTest(
        'Get response messages',
        is_array($messages) && count($messages) > 0,
        "Message count: " . count($messages),
        $messages
    );
} catch (Exception $e) {
    logTest(
        'Get response messages',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.6: Get sent request info.
try {
    $simpleClient->request('post', '/api/v1/mirror', ['test' => 'data']);
    $sent = $simpleClient->getSent();

    logTest(
        'Get sent request as object',
        is_object($sent) && isset($sent->method) && isset($sent->endpoint),
        "Method: {$sent->method}, Endpoint: {$sent->endpoint}",
        $sent
    );
} catch (Exception $e) {
    logTest(
        'Get sent request as object',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.7: Get sent headers.
try {
    $simpleClient->request('post', '/api/v1/ping');
    $headersArray = $simpleClient->getSentHeaders(true);
    $headersString = $simpleClient->getSentHeaders(false);

    logTest(
        'Get sent headers (array vs string)',
        is_array($headersArray) && is_string($headersString),
        "Array count: " . count($headersArray),
        $headersArray
    );
} catch (Exception $e) {
    logTest(
        'Get sent headers (array vs string)',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.8: Get sent payload.
try {
    $testData = ['test' => 'payload'];
    $simpleClient->request('post', '/api/v1/mirror', $testData);
    $payloadObj = $simpleClient->getSentPayload(true);
    $payloadString = $simpleClient->getSentPayload(false);

    logTest(
        'Get sent payload (object vs string)',
        is_object($payloadObj) && is_string($payloadString) && $payloadObj->test === 'payload',
        "Payload test field: " . ($payloadObj->test ?? 'N/A')
    );
} catch (Exception $e) {
    logTest(
        'Get sent payload (object vs string)',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.9: Get response headers.
try {
    $simpleClient->request('post', '/api/v1/ping');
    $headersArray = $simpleClient->getResponseHeaders(true);
    $headersString = $simpleClient->getResponseHeaders(false);

    logTest(
        'Get response headers (array vs string)',
        is_array($headersArray) && is_string($headersString),
        "Array count: " . count($headersArray)
    );
} catch (Exception $e) {
    logTest(
        'Get response headers (array vs string)',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 4.10: Get cURL info.
try {
    $simpleClient->request('post', '/api/v1/ping');
    $curlInfo = $simpleClient->getCurlInfo();

    logTest(
        'Get cURL info',
        is_array($curlInfo) && isset($curlInfo['url']),
        "Total time: " . ($curlInfo['total_time'] ?? 'N/A') . "s"
    );
} catch (Exception $e) {
    logTest(
        'Get cURL info',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// ==============================================================================
// SECTION 5: DEBUGGING FEATURES TESTS
// ==============================================================================
displaySection('SECTION 5: DEBUGGING FEATURES TESTS');

// Test 5.1: Debug mode basic usage.
try {
    $debugClient = new SparkyApiClient(API_HOST, API_KEY);
    $debugClient->acceptUnsafeCertificatesByDisablingCurlSslVerifyPeer();

    $debugClient->debugStart();
    $debugClient->request('post', '/api/v1/ping');
    $debugClient->request('post', '/api/v1/heartbeat');
    $debugClient->debugStop();

    $debugData = $debugClient->debugGet();

    logTest(
        'Debug mode basic usage',
        isset($debugData['requests']['count']) && $debugData['requests']['count'] === 2,
        "Request count: " . ($debugData['requests']['count'] ?? 'N/A'),
        $debugData
    );
} catch (Exception $e) {
    logTest(
        'Debug mode basic usage',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 5.2: Debug mode with reset.
try {
    $debugClient = new SparkyApiClient(API_HOST, API_KEY);
    $debugClient->acceptUnsafeCertificatesByDisablingCurlSslVerifyPeer();

    $debugClient->debugStart();
    $debugClient->request('post', '/api/v1/ping');
    $debugClient->request('post', '/api/v1/heartbeat');
    $debugClient->debugReset();
    $debugClient->request('post', '/api/v1/version');
    $debugClient->debugStop();

    $debugData = $debugClient->debugGet();

    logTest(
        'Debug mode with reset',
        isset($debugData['requests']['count']) && $debugData['requests']['count'] === 1,
        "Request count after reset: " . ($debugData['requests']['count'] ?? 'N/A'),
        $debugData
    );
} catch (Exception $e) {
    logTest(
        'Debug mode with reset',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 5.3: Debug mode time tracking.
try {
    $debugClient = new SparkyApiClient(API_HOST, API_KEY);
    $debugClient->acceptUnsafeCertificatesByDisablingCurlSslVerifyPeer();

    $debugClient->debugStart();
    $debugClient->request('post', '/api/v1/ping');
    $debugClient->debugStop();

    $debugData = $debugClient->debugGet();

    logTest(
        'Debug mode time tracking',
        isset($debugData['requests']['time']) && $debugData['requests']['time'] > 0,
        "Total time: " . ($debugData['requests']['time'] ?? 'N/A') . "s"
    );
} catch (Exception $e) {
    logTest(
        'Debug mode time tracking',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 5.4: Debug mode trace messages.
try {
    $debugClient = new SparkyApiClient(API_HOST, API_KEY);
    $debugClient->acceptUnsafeCertificatesByDisablingCurlSslVerifyPeer();

    $debugClient->debugStart();
    $debugClient->request('post', '/api/v1/ping');
    $debugClient->debugStop();

    $debugData = $debugClient->debugGet();

    logTest(
        'Debug mode trace messages',
        isset($debugData['requests']['trace']) && is_array($debugData['requests']['trace']) && count($debugData['requests']['trace']) > 0,
        "Trace count: " . (count($debugData['requests']['trace']) ?? 'N/A')
    );
} catch (Exception $e) {
    logTest(
        'Debug mode trace messages',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// ==============================================================================
// SECTION 6: EDGE CASES AND ERROR HANDLING TESTS
// ==============================================================================
displaySection('SECTION 6: EDGE CASES AND ERROR HANDLING TESTS');

// Test 6.1: Request to non-existent endpoint.
try {
    $simpleClient->request('post', '/api/v1/non-existent-endpoint');
    $curlInfo = $simpleClient->getCurlInfo();
    $httpCode = $curlInfo['http_code'] ?? 0;

    logTest(
        'Request to non-existent endpoint',
        $httpCode === 404,
        "HTTP Status from cURL: {$httpCode} (expected 404)"
    );
} catch (Exception $e) {
    logTest(
        'Request to non-existent endpoint',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 6.2: Empty payload handling.
try {
    $simpleClient->request('post', '/api/v1/mirror', []);
    $status = $simpleClient->getResponseStatus();

    logTest(
        'Empty payload handling',
        $status === 200,
        "HTTP Status: {$status}"
    );
} catch (Exception $e) {
    logTest(
        'Empty payload handling',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 6.3: Large payload handling.
try {
    $largeData = [];
    for ($i = 0; $i < 100; $i++) {
        $largeData["key_{$i}"] = "value_{$i}";
    }

    $simpleClient->request('post', '/api/v1/mirror', $largeData);
    $status = $simpleClient->getResponseStatus();
    $data = $simpleClient->getResponseData();

    logTest(
        'Large payload handling (100 keys)',
        $status === 200 && isset($data->key_0) && isset($data->key_99),
        "HTTP Status: {$status}, Data keys count: " . count((array)$data)
    );
} catch (Exception $e) {
    logTest(
        'Large payload handling (100 keys)',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 6.4: Nested data structures.
try {
    $nestedData = [
        'level1' => [
            'level2' => [
                'level3' => [
                    'value' => 'deep_value',
                ],
            ],
        ],
    ];

    $simpleClient->request('post', '/api/v1/mirror', $nestedData);
    $status = $simpleClient->getResponseStatus();
    $data = $simpleClient->getResponseData();

    logTest(
        'Nested data structures',
        $status === 200 && $data->level1->level2->level3->value === 'deep_value',
        "HTTP Status: {$status}"
    );
} catch (Exception $e) {
    logTest(
        'Nested data structures',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// Test 6.5: Special characters in data.
try {
    $specialData = [
        'special_chars' => 'àéîöü ñ 中文 日本語 🚀',
        'symbols' => '!@#$%^&*()_+-=[]{}|;:",.<>?',
    ];

    $simpleClient->request('post', '/api/v1/mirror', $specialData);
    $status = $simpleClient->getResponseStatus();
    $data = $simpleClient->getResponseData();

    logTest(
        'Special characters in data',
        $status === 200 && $data->special_chars === 'àéîöü ñ 中文 日本語 🚀',
        "HTTP Status: {$status}"
    );
} catch (Exception $e) {
    logTest(
        'Special characters in data',
        false,
        'Request failed: ' . $e->getMessage()
    );
}

// ==============================================================================
// SECTION 7: SIGNED REQUEST INTEGRITY TESTS
// ==============================================================================
displaySection('SECTION 7: SIGNED REQUEST INTEGRITY TESTS');

// Test 7.1: Verify signed request headers are present.
try {
    $signedClient->request('post', '/api/v1/mirror', ['test' => 'data']);
    $sentHeaders = $signedClient->getSentHeaders(true);

    $hasSignature = isset($sentHeaders['X-Api-Signature']);
    $hasNonce = isset($sentHeaders['X-Api-Nonce']);
    $hasTimestamp = isset($sentHeaders['X-Api-Timestamp']);

    logTest(
        'Signed request headers presence',
        $hasSignature && $hasNonce && $hasTimestamp,
        "Signature: " . ($hasSignature ? 'Present' : 'Missing') . ", Nonce: " . ($hasNonce ? 'Present' : 'Missing') . ", Timestamp: " . ($hasTimestamp ? 'Present' : 'Missing'),
        $sentHeaders
    );
} catch (Exception $e) {
    logTest(
        'Signed request headers presence',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 7.2: Verify nonce is unique for each request.
try {
    $signedClient->request('post', '/api/v1/ping');
    $headers1 = $signedClient->getSentHeaders(true);
    $nonce1 = $headers1['X-Api-Nonce'] ?? '';

    // Wait a moment to ensure different timestamp.
    usleep(10000);

    $signedClient->request('post', '/api/v1/ping');
    $headers2 = $signedClient->getSentHeaders(true);
    $nonce2 = $headers2['X-Api-Nonce'] ?? '';

    logTest(
        'Nonce uniqueness across requests',
        $nonce1 !== $nonce2 && !empty($nonce1) && !empty($nonce2),
        "Nonce 1: {$nonce1}, Nonce 2: {$nonce2}"
    );
} catch (Exception $e) {
    logTest(
        'Nonce uniqueness across requests',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// Test 7.3: Verify timestamp format.
try {
    $signedClient->request('post', '/api/v1/ping');
    $headers = $signedClient->getSentHeaders(true);
    $timestamp = $headers['X-Api-Timestamp'] ?? '';

    // Check if timestamp is in ISO 8601 format.
    $isValidFormat = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $timestamp);

    logTest(
        'Timestamp format validation (ISO 8601)',
        $isValidFormat === 1,
        "Timestamp: {$timestamp}"
    );
} catch (Exception $e) {
    logTest(
        'Timestamp format validation (ISO 8601)',
        false,
        'Test failed: ' . $e->getMessage()
    );
}

// ==============================================================================
// TEST SUMMARY
// ==============================================================================
$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, fn($result) => $result['success']));
$failedTests = $totalTests - $passedTests;
$successRate = ($totalTests > 0) ? round(($passedTests / $totalTests) * 100, 2) : 0;

echo "\n";
echo str_repeat('─', 60) . "\n";
echo "\033[1mRESULTS:\033[0m {$totalTests} tests • ";
echo "\033[32m{$passedTests} passed\033[0m • ";
echo "\033[31m{$failedTests} failed\033[0m • ";
echo "{$successRate}%\n";
echo str_repeat('─', 60) . "\n";

if ($failedTests > 0) {
    echo "\n\033[31m\033[1mFailed tests:\033[0m\n";
    foreach ($testResults as $result) {
        if (!$result['success']) {
            echo "  ✗ {$result['name']} — {$result['message']}\n";
        }
    }
    echo "\n";
}

// Exit with appropriate code.
exit($failedTests > 0 ? 1 : 0);
