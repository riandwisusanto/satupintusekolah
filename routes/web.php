<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

// execpt storage
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!storage).*$');
