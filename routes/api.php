<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PropertyControllerApi;

Route::get('getStartData', [PropertyControllerApi::class, 'getStartData']);
Route::get('suggestions', [PropertyControllerApi::class, 'suggestions']);
Route::get('search', [PropertyControllerApi::class, 'search']);
