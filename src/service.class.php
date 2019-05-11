<?php

/**
 * Base class for services.
 */
class Service {
    public $app;
    
    
    public function __construct(App $app) {
        $this->app = $app;
    }
}
