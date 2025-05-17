<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('log_json')) {
    function log_json(...$obj)
    {
        Log::info(json_encode($obj, JSON_PRETTY_PRINT));
    }
}
