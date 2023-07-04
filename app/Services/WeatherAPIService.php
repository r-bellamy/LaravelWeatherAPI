<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

// WeatherAPI.com API Class
class WeatherAPIService {

    // User-specific API Key
    const API_KEY = '3796c092f5894852aac222922230307';

    /**
     * Get the historical weather forecast for the past 3 days for a location.
     * 
     * @param string $location Postcode or lat,lng
     * @return WeatherAPIServiceResult Returns result
     */
    public static function getHistoricalForecast(string $location) {
        // Generate dates for the past 3 days to query the WeatherAPI.com API with.
        $dates = [
            strtotime("-3 day"),
            strtotime("-2 day"),
            strtotime("-1 day")
        ];

        // Store results for each day in an array
        $results = [];
        
        // Query each date against WeatherAPI.com history API.
        foreach ($dates as $date) {
            // Formated date as Y-m-d as expected by WeatherAPI.com.
            $dateFrom = date("Y-m-d", $date);

            // Perform the API query.
            $urlQuery = http_build_query([
                'key' => self::API_KEY,
                'q' => $location,
                'dt' => $dateFrom
            ]);
            $url = 'http://api.weatherapi.com/v1/history.json?' . $urlQuery;

            $result = self::getUrl($url);

            // If error occurred, return the error value immediately.
            if ($result->getError()) {
                return $result;
            } else {
                $results[] = $result;
            }
        }

        // Store the specific location WeatherAPI.com is giving the weather for.
        $location = NULL;
        
        // Store each historical forecast by day in an array.
        $historicalDays = [];
        foreach ($results as $result) {
            // Set location from the first API query if not yet set.
            if (!$location) {
                $location = $result->getResult()->location;
            }

            // Extract the date, condition, avg temp (c), avg humidity and max wind (mph)
            // from each daily forecast.
            foreach ($result->getResult()->forecast->forecastday as $forecastDay) {
                $date = $forecastDay->date;
                $avgTempC = $forecastDay->day->avgtemp_c;
                $avgHumidity = $forecastDay->day->avghumidity;
                $maxWindMph = $forecastDay->day->maxwind_mph;
                $condition = $forecastDay->day->condition->text;

                $historicalDays[] = [
                    'date' => $date,
                    'condition' => $condition,
                    'average_temperature_c' => $avgTempC,
                    'average_humidity' => $avgHumidity,
                    'maximum_wind_mph' => $maxWindMph
                ];
            }
        }

        // Update the result object with our custom-extracted data.
        $newResult = [
            'location' => $location,
            'forecast' => $historicalDays
        ];
        $result->setResult($newResult);

        return $result;
    }

    // 
    
    /**
     * Get the current weather for a location.
     * 
     * @param string $location Postcode or lat,lng
     * @return WeatherAPIServiceResult Returns result
     */
    public static function getCurrentWeather(string $location) {
        // Perform the API query.
        $urlQuery = http_build_query([
            'key' => self::API_KEY,
            'q' => $location,
            'aqi' => 'no'
        ]);

        $url = 'http://api.weatherapi.com/v1/current.json?' . $urlQuery;

        $ret = self::getUrl($url);
        
        // If no error occurred, extract the location, temp (c), humidity, 
        // wind (mph) and condition from the forecast.
        if (!$ret->getError()) {
            $location = $ret->getResult()->location;
            $tempC = $ret->getResult()->current->temp_c;
            $humidity = $ret->getResult()->current->humidity;
            $windMph = $ret->getResult()->current->wind_mph;
            $condition = $ret->getResult()->current->condition->text;

            $newResult = [
                'location' => $location,
                'condition' => $condition,
                'temperature_c' => $tempC,
                'humidity' => $humidity,
                'wind_mph' => $windMph
            ];
            
            // Update the result object with our custom-extracted data.
            $ret->setResult($newResult);
        }

        return $ret;
    }

    // 
    
    /**
     * Get the 3 day forecast for a location.
     * 
     * @param string $location Postcode or lat,lng
     * @return WeatherAPIServiceResult Returns result
     */
    public static function getForecast(string $location) {
        // Perform the API query.
        $urlQuery = http_build_query([
            'key' => self::API_KEY,
            'q' => $location,
            'days' => 3,
            'aqi' => 'no',
            'alerts' => 'no'
        ]);
        $url = 'http://api.weatherapi.com/v1/forecast.json?' . $urlQuery;

        $ret = self::getUrl($url);
        
         // If no error occurred, extract the location, date, avg temp (c), 
         // avg humidity, max wind speed (mph) and condition from the forecast.
        if (!$ret->getError()) {
            $location = $ret->getResult()->location;

            $forecastDays = [];
            foreach ($ret->getResult()->forecast->forecastday as $forecastDay) {
                $date = $forecastDay->date;
                $avgTempC = $forecastDay->day->avgtemp_c;
                $avgHumidity = $forecastDay->day->avghumidity;
                $maxWindMph = $forecastDay->day->maxwind_mph;
                $condition = $forecastDay->day->condition->text;

                $forecastDays[] = [
                    'date' => $date,
                    'condition' => $condition,
                    'average_temperature_c' => $avgTempC,
                    'average_humidity' => $avgHumidity,
                    'maximum_wind_mph' => $maxWindMph
                ];
            }

            $newResult = [
                'location' => $location,
                'forecast' => $forecastDays
            ];
            $ret->setResult($newResult);
        }

        return $ret;
    }

    /**
     * Helper function to fetch URL from the WeatherAPI.com API,
     * returning an object containing the response and any error message.
     * 
     * @param string $url
     * @return \App\Services\WeatherAPIServiceResult Contains the result and any error message.
     */
    private static function getUrl(string $url) {
        $client = new Client(); //GuzzleHttp\Client
        $error = NULL;
        $result = NULL;

        try {
            $response = $client->request('GET', $url);
            $result = json_decode($response->getBody());
        } catch (ClientException $e) {
            $json = json_decode($e->getResponse()->getBody());
            $error = $json->error->message;
        }

        return new WeatherAPIServiceResult($error, $result);
    }
}
