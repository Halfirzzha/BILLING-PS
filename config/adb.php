<?php

return [
    'enabled' => env('ADB_ENABLED', true),
    'binary' => env('ADB_BINARY', 'adb'),
    'browser_package' => env('ADB_BROWSER_PACKAGE', 'com.android.chrome'),
    'base_url' => env('APP_URL', 'http://127.0.0.1:8001'),
];
