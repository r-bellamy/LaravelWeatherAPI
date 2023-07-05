<?php

namespace App\Services;

/**
 * Result class for use by WeatherAPIService Class
 */
class WeatherAPIServiceResult {

    private $error;
    private $result;

    public function __construct($error, $result) {
        $this->error = $error;
        $this->result = $result;
    }

    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = $result;
    }
}
