<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WeatherAPIService;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\HALResourcesInterface;
use Illuminate\Support\Facades\Route;

/**
 * Weather API Controller
 */
class WeatherAPIController extends Controller implements HALResourcesInterface {

    /**
     * Get historical weather forecast for the past 3 days for a location.
     * 
     * @param Request $request
     * @return type
     */
    public function historicalForecast(Request $request) {
        return $this->createJSONResponse($request, function ($location) {
                    return WeatherAPIService::getHistoricalForecast($location);
                });
    }

    /**
     * Get current weather for a location.
     * 
     * @param Request $request
     * @return type
     */
    public function current(Request $request) {
        return $this->createJSONResponse($request, function ($location) {
                    return WeatherAPIService::getCurrentWeather($location);
                });
    }

    /**
     * Get weather forecast for next 3 days for a location.
     * 
     * @param Request $request
     * @return type
     */
    public function forecast(Request $request) {
        return $this->createJSONResponse($request, function ($location) {
                    return WeatherAPIService::getForecast($location);
                });
    }

    /**
     * Helper function to generate the JSON responses necessary for this class.
     * 
     * @param type $request Request object to extract the weather location from.
     * @param type $callback A callback function which passes the extracted location
     *                       to a function in the WeatherAPIService class which
     *                       returns an instance of WeatherAPIServiceResult.
     * @return type
     */
    private function createJSONResponse($request, $callback) {
        $location = NULL;

        // Proceed if client specified a location in the request.
        if ($request->has('location')) {
            $location = $request->input('location');

            // Pass location to a function which returns an instance of 
            // WeatherAPIServiceResult.
            $result = $callback($location);

            // If the WeatherAPI.com API returns an error message, 
            // return it along with a 404 (not found) status
            // as we were not able to find the weather for the location.
            if ($result->getError()) {
                return response()->json([
                            'error' => $result->getError()
                                ], Response::HTTP_NOT_FOUND);
            } else {
                // Otherwise return a successful result                
                return response()->json($result->getResult());
            }
        } else {
            // Return 400 status if client did not provide a location
            // in the request.
            return response()->json([
                        'error' => 'Location not specified'
                            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get HAL resources for this controller
     * 
     * @return type
     */
    public function getHALResources() {
        $routes = Route::getRoutes();

        $links = [
            'self' => [
                'href' => url(Route::current()->uri)
            ],
            'historical_forecast' => [
                'href' => url($routes->getByName('WeatherAPIController.historicalForecast')->uri)
            ],
            'current' => [
                'href' => url($routes->getByName('WeatherAPIController.current')->uri)
            ],
            'forecast' => [
                'href' => url($routes->getByName('WeatherAPIController.forecast')->uri)
            ]
        ];

        // Remove any links which are a duplicate of 'self' so the same uri is not listed twice.
        $links = array_filter($links, function ($v, $k) use ($links) {
            return $v['href'] !== $links['self']['href'] || $k === 'self';
        }, ARRAY_FILTER_USE_BOTH);

        return [
            '_links' => $links
        ];
    }
}
