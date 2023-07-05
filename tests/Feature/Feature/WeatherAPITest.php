<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use \App\Models\User;
use JWTAuth;

/**
 * Test Weather API endpoints
 */
class WeatherAPITest extends TestCase {
    /**
     * Test historical forecast api endpoint returns 3 days of forecast data 
     * for the correct location.
     */
    public function testHistoricalWeatherForecastResponse() {
        $this->json('GET', 'api/weather/historical?location=cf454py', headers: $this->getJWTHeader())
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('location.name', 'Mountain Ash')
                ->assertJsonPath('forecast', fn($o) => is_array($o) && count($o) === 3);
    }

    /**
     * Test current weather forecast api endpoint returns current weather
     * for the correct location.
     */
    public function testCurrentWeatherForecastResponse() {
        $this->json('GET', 'api/weather/current?location=cf454py', headers: $this->getJWTHeader())
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('location.name', 'Mountain Ash')
                ->assertJsonPath('condition', fn($o) => is_string($o))
                ->assertJsonPath('temperature_c', fn($o) => is_numeric($o))
                ->assertJsonPath('humidity', fn($o) => is_numeric($o))
                ->assertJsonPath('wind_mph', fn($o) => is_numeric($o));
    }

    /**
     * Test forecast api endpoint returns 3 days of forecast data 
     * for the correct location.
     */
    public function testWeatherForecastResponse() {
        $this->json('GET', 'api/weather/forecast?location=cf454py', headers: $this->getJWTHeader())
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('location.name', 'Mountain Ash')
                ->assertJsonPath('forecast', fn($o) => is_array($o) && count($o) === 3);
    }

    /**
     * Return JWT header which includes an authorization token for admin user,
     * so that we can test API functions restricted to logged in users.
     */
    private function getJWTHeader() {
        $admin = User::findOrFail(1);
        $adminJWTToken = JWTAuth::fromUser($admin);
        return ['Authorization' => 'Bearer ' . $adminJWTToken];
    }
}
