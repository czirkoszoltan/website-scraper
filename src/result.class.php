<?php

/**
 * Contains the result of the scraping.
 * The Reader will fill it with content as the configuration file
 * specifies. Should be extended to reflect the content and
 * override the validate() function.
 */
class Result {
    public function validate() {
    }
    
    protected function check(bool $condition, string $errormessage) {
        if (!$condition)
            throw new \RuntimeException($errormessage);
    }
}
