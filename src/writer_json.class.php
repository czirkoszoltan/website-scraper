<?php

/**
 * Dumps the result in JSON format.
 * 
 * This writer can append to a JSON array stored in a file.
 * The file name is specified with the jsonoutfile configuration
 * parameter. If the file does not exist, then a new file is
 * created. Otherwise it should contain a JSON array, to which
 * the result is appended.
 * 
 * If no file name is given in jsonoutfile, then the contents
 * of the Result object is printed to the standard output in
 * JSON format.
 */
class Writer_JSON extends Writer {
    public function write(Result $result) {
        $jsonoutfile = $this->app->config->jsonoutfile ?? null;
        
        /* if no file given, write to stdout */
        if ($jsonoutfile === null) {
            echo json_encode($result,  Config::$JSON_PARAMS), "\n";
            return;
        }
        
        /* otherwise write to file */
        try {
            $json = $this->app->files->file_get_contents($jsonoutfile);
        } catch (\Exception $e) {
            $json = "[]";
        }
        $objects = $this->app->files->json_decode($json);
        $objects[] = (object) (array) $result;
        $json = json_encode($objects, Config::$JSON_PARAMS);
        $this->app->files->file_put_contents($jsonoutfile, $json);
    }
}

