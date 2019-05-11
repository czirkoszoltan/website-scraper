<?php

/**
 * Dumps the result in PHP format.
 * 
 * This writer can append to a PHP array stored in a file.
 * The file name is specified with the phpoutfile configuration
 * parameter. If the file does not exist, then a new file is
 * created. Otherwise it should contain a PHP array, to which
 * the result is appended.
 * 
 * If no file name is given in phpoutfile, then the contents
 * of the Result object is printed to the standard output in
 * PHP format.
 */
class Writer_PHP extends Writer {
    public function write(Result $result) {
        $phpoutfile = $this->app->config->phpoutfile ?? null;
        
        /* if no file given, write to stdout */
        if ($phpoutfile === null) {
            var_export((array) $result);
            return;
        }
        
        /* otherwise write to file */
        try {
            $php = $this->app->files->file_get_contents($phpoutfile);
        } catch (\Exception $e) {
            $php = "array()";
        }
        $objects = eval("return " . $php . ";");
        $objects[] = (array) $result;
        $php = var_export($objects, true);
        $this->app->files->file_put_contents($phpoutfile, $php);
    }
}

