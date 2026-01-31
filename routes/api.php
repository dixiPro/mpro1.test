<?php

use App\Http\Controllers\PropertyControllerApi;
use Illuminate\Support\Facades\Route;

Route::get('/getStartData', [PropertyControllerApi::class, 'getStartData']);
Route::get('/suggestions', [PropertyControllerApi::class, 'suggestions']);
Route::get('/search', [PropertyControllerApi::class, 'search']);
