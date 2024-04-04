<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

function authUser(bool $get_id = false)
{
    if (!$get_id) {
        return auth()->user();
    }
    return auth()->user()->id;
}

function saveApiErrorLog($messageType, $exception, $filename = 'laravel')
{
    if ($filename == 'laravel') {
        $filename = class_basename(Route::current()->controller);
    }
    $message = $exception->getMessage();
    Log::build([
        'driver' => 'single',
        'path' => storage_path('logs/api/' . date('Y-m-d') . '/' . $filename . '.log'),
    ])->$messageType([
        'method' => getCurrentMethodName(),
        'controller' => class_basename(Route::current()->controller),
        'message' => $message
    ]);

    $slack_error_data = [
        'error_from' => 'API',
        'method' => getCurrentMethodName(),
        'controller' => class_basename(Route::current()->controller),
        'message' => $message
    ];
    Log::channel('slack')->$messageType($slack_error_data);

}
function getCurrentMethodName(): string
{
    return Route::current()->getActionMethod();
}
