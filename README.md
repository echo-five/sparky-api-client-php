# Sparky API Client PHP

[![License](https://img.shields.io/github/license/echo-five/sparky-api-client-php?label=Licence&style=flat-square)](https://github.com/echo-five/sparky-api-client-php/blob/master/LICENSE) ![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white) ![Size](https://img.shields.io/github/languages/code-size/echo-five/sparky-api-client-php?label=Size&style=flat-square)

A PHP client library for the Sparky API.

## Menu

- [Requirements](#requirements)
- [Installation](#installation)
    * [How to install?](#how-to-install)
    * [How to update?](#how-to-update)
    * [How to remove?](#how-to-remove)
- [Get started](#get-started)
- [Features](#features)
    * [Simple request vs Signed request](#simple-request-vs-signed-request)
    * [Debugging](#debugging)
- [Available methods](#available-methods)
    * [Request](#request)
    * [Get Request Response](#get-request-response)
    * [Get Request Response Status](#get-request-response-status)
    * [Get Request Response Data](#get-request-response-data)
    * [Get Request Response Messages](#get-request-response-messages)
    * [Get Request Info](#get-request-info)
    * [Debug Start](#debug-start)
    * [Debug Stop](#debug-stop)
    * [Debug Get](#debug-get)
    * [Debug Reset](#debug-reset)
    * [Accept Unsafe Certificates](#accept-unsafe-certificates)
- [License](#license)

## Requirements

- PHP 8.3 or higher with cURL, JSON, Sodium and Ctype extensions

## Installation

### How to install?

This package can be installed via Composer:

```shell
composer require echo-five/sparky-api-client-php
```

### How to update?

Use the following command to update this package only:

```shell
composer update echo-five/sparky-api-client-php
```

### How to remove?

This package can be uninstalled via Composer:

```shell
composer remove echo-five/sparky-api-client-php
```

## Get started

Prerequisites

- The library has been installed via Composer.
- You have a valid API Key.
- You have a valid API Signature Key (to "sign" the request, see [here](#simple-request-vs-signed-request)).

Create a new blank PHP file and copy the code below:

```php
<?php

// Load Composer autoloader.
require 'vendor/autoload.php';

// Use declaration.
use SparkyApiClient\SparkyApiClient;

// Try to execute the API request.
try {
    // Initialize.
    $sparkyApi = new SparkyApiClient('SPARKY_API_HOST', 'SPARKY_API_KEY');
    
    // Do a request.
    $sparkyApi->request('post', '/endpoint', [
        'foo' => 'Bar',
        'biz' => 'Buz',
    ]);
    
    // Get the request response.
    $requestResponse = $sparkyApi->getRequestResponse();
    
    // Dump.
    var_dump($requestResponse);
}
catch (Exception $e) {
    // Show error message.
    echo 'Error: ' . $e->getMessage();
}
```

*This example is stored in the project and can be downloaded here: [GetStartedSimpleRequest.php](https://github.com/echo-five/sparky-api-client-php/blob/master/examples/GetStartedSimpleRequest.php)*

## Features

### Simple request vs Signed request

The library allows two types of requests: 

- **Simple requests (unsigned)** 
    The request is validated only using your API key.
- **Signed requests**
    Your request is signed using asymmetric cryptography.
    This allows to verify both the authenticity and integrity of the data.

Signed requests, although a bit slower, are therefore more secure than simple requests.

#### How it works

- When you create an API key on the platform, a cryptographic key pair is generated as well.
    - **The public key** is stored on the API side and linked to your account.
    - **The private key** (the API Signature Key) is shown to you once.
        - The system does not store it and cannot regenerate it.
            You must keep it secure.
- You use the API Signature Key to sign your requests before they are sent.
- The system verifies each signature using your registered public key.
- If the signature is invalid, the request is not processed.

**Signing requests is very straightforward!**
The only thing to do is to provide your API Signature Key when you instantiate the library.
Then all requests will be automatically signed!

```php
// Initialize with signature key (private key) for signed requests.
$sparkyApi = new SparkyApiClient('SPARKY_API_HOST', 'SPARKY_API_KEY', 'SPARKY_API_SIGNATURE_KEY');
```

*This example is stored in the project and can be downloaded here: [GetStartedSignedRequest.php](https://github.com/echo-five/sparky-api-client-php/blob/master/examples/GetStartedSignedRequest.php)*

#### IMPORTANT

- **Store** your API Key and API Signature Key **securely** (environment variables, secrets manager, etc.).
- **Never share** your API Key or API Signature Key, and never commit them to version control.

### Debugging

Troubleshooting an API can sometimes be challenging.
The library includes a debugging mode to help you better understand how requests are built and sent.

The debugging mode allows to:

- See the time consumption of each request.
- See how many requests were executed.
- See the execution order of the requests.

**Using the debugging mode is very easy!**
Just start it at a specific breakpoint and stop it at another.

```php
<?php

// Load Composer autoloader.
require 'vendor/autoload.php';

// Use declaration.
use SparkyApiClient\SparkyApiClient;

// Try to execute the API request.
try {
    // Initialize.
    $sparkyApi = new SparkyApiClient('SPARKY_API_HOST', 'SPARKY_API_KEY');
    
    // Start the debugging mode.
    $sparkyApi->debugStart();
    
    // Do a request.
    $sparkyApi->request('post', '/endpoint', [
        'foo' => 'Bar',
        'biz' => 'Buz',
    ]);
    
    // Stop the debugging mode.
    $sparkyApi->debugStop();
    
    // Get the request response.
    $requestResponse = $sparkyApi->getRequestResponse();
    
    // Dumps.
    var_dump($requestResponse);
    var_dump($sparkyApi->debugGet());
}
catch (Exception $e) {
    // Show error message.
    echo 'Error: ' . $e->getMessage();
}
```

*See [Available methods](#available-methods) section for more debugging options.*
*This example is stored in the project and can be downloaded here: [GetStartedDebugging.php](https://github.com/echo-five/sparky-api-client-php/blob/master/examples/GetStartedDebugging.php)*

Here is an example of the debugging information:

```
// Result:
Array
(
    [requests] => Array
        (
            [time] => 0.218735
            [count] => 1
            [trace] => Array
                (
                    [2025-01-01T12:57:09.603425Z] => Start debug.
                    [2025-01-01T12:57:09.822565Z] => Request | https://example.com/endpoint
                    [2025-01-01T12:57:09.822592Z] => Stop debug.
                )
        )
)
```

## Available methods

### Request

This method allows to make an API request.

> request(string $requestType, string $requestEndpoint, array <$requestParams>, string <$requestMode>)

- The `requestType` argument defines the HTTP method to use.  
Allowed values are `GET`, `POST`, `PUT`, `PATCH`, and `DELETE`.  
This argument is mandatory.

- The `requestEndpoint` argument defines the endpoint to call.  
This is a URI, e.g.: `/endpoint` or `/api/v1/mirror`   
This argument is mandatory.

- The `requestParams` argument defines the data to send to the endpoint.  
This is a key/value array, by default no data is sent.  
For `GET` and `DELETE`: sent as query string parameters.  
For `POST`, `PUT`, `PATCH`: sent as request body.  
This argument is optional.

- The `requestMode` argument defines the body encoding format.  
Allowed values are `JSON` (default), `FORM`, and `HTTP`.  
Only applicable for `POST`, `PUT`, and `PATCH` requests.  
This argument is optional.

This method returns the class instance itself, not the result of the request.  
The request result must be retrieved using another method (`getRequestResponse`).  
For convenience, this method is chainable.

Example:

```php
// Do a request and get the request response.
$requestResponse = $sparkyApi->request('post', '/endpoint')->getRequestResponse();
```

### Get Request Response

This method allows to get the response of the request.

> getRequestResponse(bool <$object>)

- The `object` argument defines if the request response must be returned as object or not.  
The API replies in JSON format, so the response is a raw JSON string.  
The `object` argument allows to get a PHP object instead of a raw JSON string.  
The `object` argument is set to `true` by default.

Usage examples:

```php
$requestResponse = $sparkyApi->getRequestResponse();      // Return a PHP object.
$requestResponse = $sparkyApi->getRequestResponse(true);  // Return a PHP object.
$requestResponse = $sparkyApi->getRequestResponse(false); // Return a raw JSON string.
```

This method returns a raw JSON string or a PHP object, depending on the passed argument.  
The request response is always a full API response.  
Here is an example:

```php
// Request:
$sparkyApi->request('post', '/endpoint', [
    'foo' => 'Bar',
    'biz' => 'Buz',
]);

// Response:
stdClass Object
(
    [status] => 200
    [data] => stdClass Object
        (
            [foo] => Bar
            [biz] => Buz
        )
    [messages] => Array
        (
            [0] => stdClass Object
                (
                    [type] => info
                    [text] => The request has input data.
                )
            [1] => stdClass Object
                (
                    [type] => info
                    [text] => The request has 2 data keys.
                    [data] => stdClass Object
                        (
                            [keys] => 2
                        )
                )
        )
)
```

### Get Request Response Status

This method allows to directly get the `[status]` property of the request response.  
The `status` is the HTTP status code associated with the response.

> getRequestResponseStatus()

This method always returns an int.

Usage example:

```php
// Get the request response status.
$requestResponseStatus = $sparkyApi->getRequestResponseStatus();
```

### Get Request Response Data

This method allows to directly get the `[data]` property of the request response.

> getRequestResponseData()

This method always returns a PHP object.

Usage example:

```php
// Get the request response data.
$requestResponseData = $sparkyApi->getRequestResponseData();
```

### Get Request Response Messages

This method allows to directly get the `[messages]` property of the request response.

> getRequestResponseMessages()

This method always returns a PHP array.

Usage example:

```php
// Get the request response messages.
$requestResponseMessages = $sparkyApi->getRequestResponseMessages();
```

### Get Request Info

This method allows to get the cURL request information.  
Each request is made using the PHP cURL extension, and this method returns the result of `curl_getinfo()`.  
See the official [PHP.net](https://www.php.net/manual/en/function.curl-getinfo.php) documentation for details.

> getRequestInfo()

This method always returns a PHP array.

Usage example:

```php
// Get the request info.
$requestInfo = $sparkyApi->getRequestInfo();
```

### Debug Start

This method allows to start the debugging mode.  
Every request executed after this method call will be taken into account for the debugging.

> debugStart()

This method returns the class instance itself.  
For convenience, this method is chainable.

Example:

```php
// Start the debugging mode.
$sparkyApi->debugStart();
```

### Debug Stop

This method allows to stop the debugging mode.  
Every request executed after this method call will not be taken into account for the debugging.

> debugStop()

This method returns the class instance itself.  
For convenience, this method is chainable.

Example:

```php
// Stop the debugging mode.
$sparkyApi->debugStop();
```

### Debug Get

This method allows to get the result of the debugging data.  
This method call doesn't stop the debugging mode, so it can be called every time needed.  
This is just a debugging output at the time "t".

> debugGet()

This method returns a PHP array.

Example:

```php
// Start the debugging mode.
$sparkyApi->debugStart();

// Do a request #1.
$sparkyApi->request('post', '/endpoint');

// Do a request #2.
$sparkyApi->request('post', '/endpoint');

// Dump the debugging data (which contains requests #1 and #2).
var_dump($sparkyApi->debugGet());

// Do a request #3.
$sparkyApi->request('post', '/endpoint');

// Do a request #4.
$sparkyApi->request('post', '/endpoint');

// Stop the debugging mode.
$sparkyApi->debugStop();

// Dump the debugging data (which contains requests #1, #2, #3, #4).
var_dump($sparkyApi->debugGet());
```

### Debug Reset

This method allows to reset the debugging data.  
This method call doesn't stop the debugging mode, but it erases all collected data.

> debugReset()

This method returns the class instance itself.  
For convenience, this method is chainable.

Example #1:

```php
// Do a request and reset the debugging data.
$sparkyApi->request('post', '/endpoint')->debugReset();
```

Example #2:

```php
// Start the debugging mode.
$sparkyApi->debugStart();

// Do a request #1.
$sparkyApi->request('post', '/endpoint');

// Do a request #2.
$sparkyApi->request('post', '/endpoint');

// Dump the debugging data (which contains requests #1 and #2).
var_dump($sparkyApi->debugGet());

// Reset the debugging data.
$sparkyApi->debugReset();

// Do a request #3.
$sparkyApi->request('post', '/endpoint');

// Do a request #4.
$sparkyApi->request('post', '/endpoint');

// Stop the debugging mode.
$sparkyApi->debugStop();

// Dump the debugging data (which contains requests #3 and #4).
var_dump($sparkyApi->debugGet());
```

### Accept Unsafe Certificates

This method allows to disable the cURL SSL verify peer.  
For example: accepting self-signed SSL certificates in development.

> acceptUnsafeCertificatesByDisablingCurlSllVerifyPeer()

**WARNING:**  
**This method is intended for API development and testing purposes only.**  
**It should NEVER be used when consuming production APIs, as it disables SSL verification.**  
**Only use this when testing against local API instances with self-signed certificates.**

This method returns the class instance itself.  
For convenience, this method is chainable.

Example:

```php
// Accept self-signed certificates when testing against local API instance.
$sparkyApi = new SparkyApiClient('https://local-api.dev', 'SPARKY_API_KEY');
$sparkyApi->acceptUnsafeCertificatesByDisablingCurlSllVerifyPeer();
$sparkyApi->request('post', '/endpoint', ['data' => 'value']);
```

## License

[MIT](https://choosealicense.com/licenses/mit/)
