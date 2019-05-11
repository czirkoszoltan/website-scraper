<?php

/**
 * Reads HTML file. Works by converting HTML to XML
 * by using Tidy, so works for XHTML and HTML as well
 * (the file may have some syntax errors as well).
 * The input files should be UTF-8 encoded.
 * 
 * Input configuration is:
 * @code
 * "imports": {
 *     "field_name": [ xpath, filters... ]
 * }
 * @endcode
 * 
 * Xpaths will usually start with "./" (direct descendant of node),
 * ".//" (any descendant of node) or "//" (anywhere in document).
 * 
 * Searches will create arrays of SimpleXmlElement objects. Use
 * the filters to have a single object, or maybe to convert it
 * to text.
 * 
 * The configuration parameter "main" can also specify an xpath.
 * If this is the case, "./" and ".//" selectors will work from
 * there, otherwise they use the full document. If "main" is
 * specified, you can still do a full-document search with "//".
 * 
 * Special "xpaths":
 * - @filename@ - the name of the imported file in an array: ["/something/abc.html"].
 */
class Reader_HTML extends Reader {
    /**
     * Parse CSV data into an array (rows) of arrays (columns).
     */
    protected function parse_inputdata(string $inputdata) {
        /* Convert text to SimpleXmlElement. */
        $xmltext = $this->fix_html_errors_in_string($inputdata);
        $xml = $this->string_to_xmlelement($xmltext);
        $this->fix_xmlelement_errors($xml);
        
        /* Find main tag. */
        $main = $xml;
        if (isset($this->app->config->main))
            $main = $this->xpath_one($xml, $this->app->config->main);
        
        /* Return ref to main tag. */
        return $main;
    }
    
    
    /**
     * Read CSV fields: path is column number.
     * Returns null for non-existing columns.
     */
    protected function read_field($data, string $path) {
        switch ($path) {
            case '@filename@':
                return [$this->app->inputfile];
            default:
                return $this->xpath($data, $path);
        }
    }
    
    
    /**
     * Xpath traversal with error exception.
     */
    private function xpath(\SimpleXMLElement $xml, string $xpath) : array {
        try {
            $elem = $xml->xpath($xpath);
            $this->app->logger->debug("Xpath traversal: %s, count=%d", $xpath, count($elem));
            return $elem;
        } catch (\Exception $e) {
            throw new \RuntimeException("Xpath traversal error: {$xpath}", 0, $e);
        }
    }
    
    
    /**
     * Xpath traversal, find exactly one element, with error exception.
     */
    private function xpath_one(\SimpleXMLElement $xml, string $xpath) : \SimpleXMLElement {
        $elem = $this->xpath($xml, $xpath);
        if (count($elem) != 1)
            throw new \RuntimeException("Xpath traversal: should find exactly one element: {$xpath}.");
        return $elem[0];
    }
    
    
    /**
     * Read string and return xmlelement.
     * The string may contain XHTML or XML text, maybe with some syntax errors.
     */
    protected function string_to_xmlelement(string $string) : \SimpleXMLElement {
        $tidy = new \tidy();
        $config = array_merge(Config::$TIDY, [
            'output-xml' => true,
            'output-html' => false,
        ]);
        $repaired = $tidy->repairString($string, $config, 'utf8');
        $repaired = str_replace(' xmlns="http://www.w3.org/1999/xhtml"', '', $repaired);
        $repaired = '<?xml version="1.0" encoding="UTF-8"?>' . $repaired;
        
        return new \SimpleXMLElement($repaired);
    }


    /**
     * Small HTML enhancements and fixes that work on the SimpleXMLElement level.
     * Override to add new fixes.
     */
    protected function fix_xmlelement_errors(\SimpleXMLElement $xml) {
        /* iframe hack, if the iframe is empty, a space is inserted,
         * so no self-closing tag appears in the output. */
        foreach ($xml->xpath(".//iframe") as $iframe) {
            if ((string) $iframe == "" && count($iframe->children()) == 0) {
                $src = $iframe->attributes()['src'];
                $this->app->logger->log("<iframe></iframe> fixed: %s", $src);
                $iframe[0] = " ";
            }
        }
    }
    

    /**
     * HTML fixes that are executed before loading the file.
     * Override to add new fixes.
     */
    protected function fix_html_errors_in_string(string $string) : string {
        /* Fixes invalid self-closing iframe: converts <iframe /></iframe> to <iframe></iframe>. */
        $string = preg_replace('@<iframe ([^>]+?) /></iframe>@', '<iframe $1></iframe>', $string);
        
        return $string;
    }
}
