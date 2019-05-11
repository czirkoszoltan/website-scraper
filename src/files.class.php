<?php

/**
 * Handler for files.
 */
class Files extends Service {
    /**
     * Wrapper for PHP file_get_contents with exception handling.
     */
    public function file_get_contents(string $filename) : string {
        $this->app->logger->debug("Read file: %s", $filename);
        $contents = @file_get_contents($filename);
        if ($contents === false)
            throw new \RuntimeException(error_get_last()['message']);
        return $contents;
    }
    

    /**
     * Wrapper for PHP file_put_contents with exception handling.
     */
    public function file_put_contents(string $filename, string $data) {
        $this->app->logger->debug("Write file: %s", $filename);
        $saved = @file_put_contents($filename, $data);
        if ($saved == false)
            throw new \RuntimeException(error_get_last()['message']);
    }
    
    
    /**
     * Wrapper for PHP json_decode with exception handling.
     * (JSON_THROW_ON_ERROR is >= PHP7.3)
     */
    public function json_decode(string $json) {
        $decoded = json_decode($json);
        if ($decoded === null)
            throw new \RuntimeException("JSON: " . json_last_error_msg());
        return $decoded;
    }
}
