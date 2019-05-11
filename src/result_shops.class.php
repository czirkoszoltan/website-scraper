<?php

/**
 * Example derived class of Result:
 * shops with addresses and GPS coordinates.
 */
class Result_Shops extends Result {
    public $name = [];
    
    public $address = [];
    
    public $gps_lat = [];
    
    public $gps_lon = [];
    
    public function validate() {
        $this->check(is_array($this->name), "Shop names: must be an array");
        $this->check(is_array($this->address), "Shop addresses: must be an array");
        $this->check(is_array($this->gps_lat), "Shop gps_lat: must be an array");
        $this->check(is_array($this->gps_lon), "Shop gps_lon: must be an array");
        $this->check(count($this->name) == count($this->address), "Equal number of shop names and addresses required");
        $this->check(count($this->name) == count($this->gps_lat), "Equal number of shop names and GPS coordinates required");
        $this->check(count($this->name) == count($this->gps_lon), "Equal number of shop names and GPS coordinates required");
    }
}
