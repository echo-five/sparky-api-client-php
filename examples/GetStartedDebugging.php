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
