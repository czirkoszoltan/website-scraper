<?php

/**
 * Logger service. Debug logging can be turned off.
 */
class Logger extends Service {
    /**
     * Log debug message, sprintf-like interface.
     */
    public function debug(string $message, string ... $params) {
        if (Config::$DEBUG) {
            $msg = sprintf($message, ... $params);
            error_log($msg);
        }
    }
    
    
    /**
     * Print error message, sprintf-like interface.
     */
    public function fatal(string $message, string ... $params) {
        $msg = sprintf($message, ... $params);
        error_log($msg);
    }
}
