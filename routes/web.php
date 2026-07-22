<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

require __DIR__.'/admin.php';
require __DIR__.'/portal.php';
require __DIR__.'/survey.php';
require __DIR__.'/api.php';
