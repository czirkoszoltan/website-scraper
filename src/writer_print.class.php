<?php

/**
 * A Writer which dumps the output to the standard output,
 * as written by PHP print_r(). For testing and experimenting.
 */
class Writer_Print extends Writer {
    public function write(Result $result) {
        print_r($result);
    }
}
