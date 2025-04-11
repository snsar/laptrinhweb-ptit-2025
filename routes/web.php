<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    Log::info('Welcome route accessed');
    return view('welcome');
});
