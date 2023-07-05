<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherAPIController;
use App\Http\Controllers\JWTAuthController;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider and all of them will
  | be assigned to the "api" middleware group. Make something great!
  |
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [JWTAuthController::class, 'register']);
Route::post('login', [JWTAuthController::class, 'login']);

Route::group(['middleware' => ['jwt.verify', 'mymiddleware']], function () {
    Route::post('logout', [JWTAuthController::class, 'logout']);

    // Get historical weather forecast
    Route::get('/weather/historical', [WeatherAPIController::class, 'historicalForecast']);

    // Get current weather
    Route::get('/weather/current', [WeatherAPIController::class, 'current']);

    // Get upcoming weather forecast
    Route::get('/weather/forecast', [WeatherAPIController::class, 'forecast']);
});

Route::group(['middleware' => ['hal.resources']], function () {

// Get historical weather forecast
    Route::get('/weather/historical', [WeatherAPIController::class, 'historicalForecast'])
            ->name('WeatherAPIController.historicalForecast');

// Get current weather
    Route::get('/weather/current', [WeatherAPIController::class, 'current'])
            ->name('WeatherAPIController.current');

// Get upcoming weather forecast
    Route::get('/weather/forecast', [WeatherAPIController::class, 'forecast'])
            ->name('WeatherAPIController.forecast');
});
