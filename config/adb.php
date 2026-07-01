<?php

return [
    'enabled' => env('ADB_ENABLED', true),
    'binary' => env('ADB_BINARY', 'adb'),
    // Native Android TV app package (relaunched by the ADB agent for recovery).
    'app_package' => env('ADB_APP_PACKAGE', 'com.billingps5.tv'),
    'base_url' => env('APP_URL', 'http://127.0.0.1'),
];
