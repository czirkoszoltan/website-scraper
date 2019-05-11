<?php

/**
 * Base class for all Readers.
 * 
 * Import configuration is read from the "import" field of the configuration,
 * which is an objects. All entries should be:
 * @code
 * "imports": {
 *     "field name": [ "path specification", "filter"... ]
 * }
 * 
 * Path specification is reader-dependent. Examples: xpath for HTML reading,
 * column number for CSV reading.
 * 
 * Filters: see the functions in the Filters class.
 */
abstract class Reader extends Service {
    /**
     * Read data from string, and return in a format that will be
     * used by read_field.
     * @return Any data that will be passed to read_field.
     */
    abstract protected function parse_inputdata(string $inputdata);
    
    
    /**
     * Read one field using the path specified.
     * @param $data Any data that was returned by parse_data().
     */
    abstract protected function read_field($data, string $path);
    
    
    /**
     * Imports data from $inputdata, and copies everything to $result,
     * as specified by the import configuration.
     */
    public function read(string $inputdata, Result $result) {
        $data = $this->parse_inputdata($inputdata);

        foreach ($this->app->config->imports as $attribute => $importarr) {
            $path = $importarr[0];
            $filters = array_slice($importarr, 1);
            $res = $this->read_field($data, $path);
            $res = $this->apply_filters($res, $filters);
            $result->$attribute = $res;
        }
    }


    /**
     * Applies all filters.
     */
    private function apply_filters($data, array $filters) {
        foreach ($filters as $filter) {
            if (!is_callable(Filters::class . '::' . $filter))
                throw new \RuntimeException("No such filter: {$filter}");
            $data = $this->app->filters->$filter($data);
        }
        return $data;
    }
}
