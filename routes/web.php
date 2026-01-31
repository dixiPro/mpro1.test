<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

//  for testing
// Route::get('/form', function () {
//     return view('form');
// });

// use App\Http\Controllers\PropertyController;

// Route::get('/search', [PropertyController::class, 'search']);
