<?php

/**
 * Read data from a CSV file.
 * 
 * Input configuration is:
 * @code
 * "imports": {
 *     "field_name": [ col_num, filters... ]
 * }
 * @endcode
 * 
 * CSV field separator can be configured with "csvseparator".
 * By default, it is ";".
 */
class Reader_CSV extends Reader {
    /**
     * Parse CSV data into an array (rows) of arrays (columns).
     */
    protected function parse_inputdata(string $inputdata) {
        /* Split input file lines and fields */
        $rows = explode("\n", $inputdata);
        $rows = array_filter($rows, function($sor) {
            return trim($sor) !== ""; 
        });
        $rows = array_map(function($sor) {
            return explode($this->app->config->csvseparator ?? ";", $sor);
        }, $rows);
        
        return $rows;
    }
    
    
    /**
     * Read CSV fields: path is column number.
     * Inserts null for non-existing columns.
     */
    protected function read_field($data, string $path) {
        $this->app->logger->debug("CSV row: %s", $path);
        return array_map(function(array $row) use ($path) {
            return $row[(int) $path] ?? null;
        }, $data);
    }
}
