<?php

/**
 * Dumps the result in CSV format.
 * 
 * The CSV output file name is given in the configuration
 * paramter called 'csvoutfile'. If this is empty, then
 * the data is written to the standard output.
 * 
 * Arrays in the result data are converted to strings,
 * delimiter is \n.
 */
class Writer_CSV extends Writer {
    public function write(Result $result) {
        $csvoutfile = $this->app->config->csvoutfile ?? "php://stdout";
        
        $f = fopen($csvoutfile, "a");
        if ($f === null)
            throw new \RuntimeError("Cannot open CSV output: {$csvoutfile}");
        fputcsv($f, $this->to_array($result));
        fclose($f);
    }
    
    
    /**
     * Convert Result object to array for fputcsv().
     * Convert array values to strings, with elements delimited by \n.
     */
    private function to_array(Result $result) : array {
        $res = [];
        foreach ($result as $key => $val) {
            if (is_array($val))
                $val = implode("\n", $val);
            $res[$key] = $val;
        }
        return $res;
    }
    
    
}
