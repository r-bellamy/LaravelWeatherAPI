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

// Public routes
Route::group(['middleware' => ['hal.resources']], function () {
    // Register route
    Route::post('register', [JWTAuthController::class, 'register'])
            ->name('JWTAuthController.register');

    // Login route
    Route::post('login', [JWTAuthController::class, 'login'])
            ->name('JWTAuthController.login');
});

// Authenticated user routes
Route::group(['middleware' => ['jwt.verify', 'hal.resources']], function () {
    // Logout route
    Route::post('logout', [JWTAuthController::class, 'logout'])->name('JWTAuthController.logout');

    // Historical weather forecast route
    Route::get('/weather/historical', [WeatherAPIController::class, 'historicalWeatherForecast'])
            ->name('WeatherAPIController.historicalWeatherForecast');

    // Current weather route
    Route::get('/weather/current', [WeatherAPIController::class, 'currentWeather'])
            ->name('WeatherAPIController.currentWeather');

    // Weather forecast route
    Route::get('/weather/forecast', [WeatherAPIController::class, 'weatherForecast'])
            ->name('WeatherAPIController.weatherForecast');
});