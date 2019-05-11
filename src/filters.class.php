<?php

/**
 * Filters class: contains all filter functions.
 * 
 * The functions can be filters (array->array, with some
 * elements missing), mappers (array->array with different
 * content) and reducers (array->object) as well.
 */
class Filters extends Service {
    /**
     * Calls funcction on $data, and returns the result.
     * If data is null, then null is returned.
     * If data is an array, then the function is applied to each element.
     */
    private function process($data, callable $func) {
        if (is_null($data))
            return null;
        if (is_array($data))
            return array_map($func, $data);
        return $func($data);
    }
    
    
    /** 
     * Checks if the array is non-empty. Returns 1
     * if the array has content, 0 otherwise.
     */
    public function exists(array $data) {
        return count($data) > 0 ? 1 : 0;
    }
    
    
    /** 
     * Throws exception if the data is empty,
     * otherwise returns it unchanged.
     */
    public function not_null($data) {
        if ($data === null)
            throw new \RuntimeException("not_null: field cannot be null.");
        return $data;
    }


    /** 
     * Convert array to single element.
     * The input array must have 1 or 0 elements. If it has 1,
     * it is returned. If 0, null is returned.
     */
    public function max_one(array $data) {
        $cnt = count($data);
        if ($cnt > 1)
            throw new \RuntimeException("max_one: array must have at most 1 element.");
        if (count($data) == 0)
            return null;
        return $data[0];
    }
    
    
    /** 
     * Returns with the first element of the array. If there are zero elements,
     * and exception is thrown.
     */
    public function first(array $data) {
        $cnt = count($data);
        if ($cnt == 0) {
            throw new \RuntimeException("first: there are zero elements in the array.");
        }
        return $data[0];
    }
    
    
    /**
     * Returns with the first element of the array, but only if there is exactly
     * one element. Otherwise an exception is thrown.
     */
    public function one(array $data) {
        $cnt = count($data);
        if ($cnt != 1)
            throw new \RuntimeException("one: there must be exactly one element in the array.");
        return $data[0];
    }


    /**
     * Convert text nodes of SimpleXmlElement to HTML text.
     * If the input is not a SimpleXmlElement, it is converted to string.
     */
    public function innerText($data) {
        return $this->process($data, function($obj) {
            if ($obj instanceof \SimpleXMLElement)
                $str = html_entity_decode(strip_tags($obj->asXML()));
            else
                $str = (string) $obj;
            $str = str_replace("\xC2\xA0", " ", $str);
            return trim($str);
        });
    }


    /** 
     * Output SimpleXmlElement as HTML, with root element.
     */
    public function HTML($data) : string {
        return $this->process($data, function(\SimpleXMLElement $data) : string {
            $string = $data->asXML();
            $tidy = new \tidy();
            $config = array_merge(ImportConfig::$TIDY, [
                'output-xml' => false,
                'output-html' => true,
                'show-body-only' => true,
            ]);
            $string = $tidy->repairString($string, $config, 'utf8');
            return $string;
        });
    }
    
    
    /** 
     * Removes outer root tags from XML text.
     */
    public function remove_root($xmlstring) {
        return $this->process($xmlstring, function(string $xmlstring) : string {
            if (trim($xmlstring) == "")
                return "";
            $pos = strpos($xmlstring, '>');
            if ($pos === false)
                throw new \RuntimeException("remove_root: no root element found");
            $xmlstring = substr($xmlstring, $pos+1);
            $pos = strrpos($xmlstring, '<');
            if ($pos === false) {
                return "";
            }
            $xmlstring = substr($xmlstring, 0, $pos);
            return $xmlstring;
        });
    }


    /** 
     * Convert SimpleXmlElement to HTML code, without root element.
     */
    public function innerHTML($data) {
        return $this->process($data, function(\SimpleXMLElement $data) : string {
            $htmltext = trim(self::remove_root(self::HTML($data)));
            if ($htmltext != "" && $htmltext[0] != "<")
                $htmltext = "<p>" . $htmltext;
            return $htmltext;
        });
    }


    /**
     * Removes class attributes.
     */
    public function remove_class($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) : \SimpleXMLElement {
            $elements = $xml->xpath(".//*[@class]");
            foreach ($elements as $elem) {
                unset($elem['class']);
            }
            return $xml;
        });
    }


    /**
     * Removes style attributes.
     */
    public function remove_style($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) : \SimpleXMLElement {
            $elements = $xml->xpath(".//*[@style]");
            foreach ($elements as $elem) {
                unset($elem['style']);
            }
            return $xml;
        });
    }
    
    
    /**
     * Convert number to float.
     */
    public function floatval($data) {
        return $this->process($data, function($data) {
            return floatval($data);
        });
    }
    
    
    /** 
     * Wraps the PHP nl2br function.
     */
    public function nl2br($data) {
        return $this->process($data, function(string $data) : string {
            return nl2br($data, false);
        });
    }
    
    
    /** 
     * Implodes array to a list like a→b→c.
     */
    public function implode_rightarrow(array $data) {
        return implode("→", $data);
    }
    
    
    /** 
     * Implodes array to a list like a;b;c.
     */
    public function implode_semicolon(array $data) {
        return implode(";", $data);
    }
    
    
    /** 
     * `23-07-2016` style date is converted to SQL date: `2016-07-23 12:00:00`.
     */
    public function dmy_date($data) {
        return $this->process($data, function(string $data) : string {
            $parsed = sscanf($data, "%d-%d-%d", $d, $m, $y);
            if ($parsed != 3)
                throw new \RuntimeException("invalid date: {$data}");
            return sprintf("%04d-%02d-%02d 12:00:00", $y, $m, $d);
        });
    }
    
    
    /**
     * `2016-07-23` style date is converted to SQL timestamp: `2016-07-23 12:00:00`.
     */
    public function ymd_date($data) {
        return $this->process($data, function(string $data) : string {
            $parsed = sscanf($data, "%d-%d-%d", $y, $m, $d);
            if ($parsed != 3)
                throw new \RuntimeException("invalid date: {$data}");
            return sprintf("%04d-%02d-%02d 12:00:00", $y, $m, $d);
        });
    }
    

    /**
     * Removes all <h1> tags, return new SimpleXmlElement object.
     */
    public function remove_h1s($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) {
            $xml = clone $xml;
            $h1s = $xml->xpath(".//h1");
            foreach ($h1s as $h1)
                unset($h1[0]);
            return $xml;
        });
    }
    
    
    /**
     * Removes all <img> tags, return new SimpleXmlElement object.
     */
    public function remove_imgs($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) {
            $xml = clone $xml;
            $imgs = $xml->xpath(".//img");
            foreach ($imgs as $img)
                unset($img[0]);
            return $xml;
        });
    }
    
    
    /**
     * Converts all non-breaking spaces to spaces.
     */
    public function all_nbsp_to_space($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) {
            $html = $xml->asXML();
            $html = str_replace("&nbsp;", " ", $html);
            $html = str_replace("\xC2\xA0", " ", $html);
            $xml = new \SimpleXMLElement($html);
            return $xml;
        });
    }
    
    
    /**
     * Removes empty <p> tags.
     */
    public function remove_empty_p($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) {
            $html = $xml->asXML();
            $html = str_replace("<p> </p>", "", $html);
            $html = str_replace("<p>\xC2\xA0</p>", "", $html);
            $html = str_replace("<p><span> </span></p>", "", $html);
            $html = str_replace("<p><span>\xC2\xA0</span></p>", "", $html);
            $xml = new \SimpleXMLElement($html);
            return $xml;
        });
    }
    
    
    /**
     * Removes <br> from the bottom of <p> and <div> tags.
     */
    public function remove_unnecessary_br($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) : \SimpleXMLElement {
            $html = $xml->asXML();
            $html = preg_replace('@(\s*<br\s*/?>\s*)*</p>@', "</p>", $html);
            $html = preg_replace('@(\s*<br\s*/?>\s*)*</div>@', "</div>", $html);
            $xml = new \SimpleXMLElement($html);
            return $xml;
        });
    }
    
    
    /**
     * Replaces all <div> tags with <p> tags.
     */
    public function change_div_to_p($data) {
        return $this->process($data, function(\SimpleXMLElement $xml) : \SimpleXMLElement {
            $html = $xml->asXML();
            $html = str_replace("<div ", "<p ", $html);
            $html = str_replace("</div>", "</p>", $html);
            $xml = new \SimpleXMLElement($html);
            return $xml;
        });
    }


    /** 
     * Get src="" attribute of element or elements.
     */
    public function attrib_src($data) {
        return $this->process($data, function(\SimpleXMLElement $obj) : string {
            return (string) $obj->attributes()['src'];
        });
    }


    /** 
     * Get content="" attribute of element or elements.
     */
    public function attrib_content($data) {
        return $this->process($data, function(\SimpleXMLElement $obj) : string {
            return (string) $obj->attributes()['content'];
        });
    }


    /** 
     * Get alt="" attribute of element or elements.
     */
    public function attrib_alt($data) {
        return $this->process($data, function(\SimpleXMLElement $obj) : string {
            return (string) $obj->attributes()['alt'];
        });
    }


    /** 
     * Get href="" attribute of element or elements.
     */
    public function attrib_href($data) {
        return $this->process($data, function(\SimpleXMLElement $obj) : string {
            return (string) $obj->attributes()['href'];
        });
    }


    /** 
     * Get data-href="" attribute of element or elements.
     */
    public function attrib_data_href($data) {
        return $this->process($data, function(\SimpleXMLElement $obj) : string {
            return (string) $obj->attributes()['data-href'];
        });
    }


    /** 
     * Get title="" attribute of element or elements.
     */
    public function attrib_title($data) {
        return $this->process($data, function(\SimpleXMLElement $obj) : string {
            return (string) $obj->attributes()['title'];
        });
    }


    /** 
     * Get data-description="" attribute of element or elements.
     */
    public function attrib_data_description($data) {
        return $this->process($data, function(\SimpleXMLElement $obj) : string {
            return (string) $obj->attributes()['data-description'];
        });
    }


    /** 
     * Reads path + query from url, eg. http://example.com/ex.html?query=123
     * becomes /ex.html?query=123.
     */
    public function url_path_and_query($data) {
        return $this->process($data, function(string $str) : string {
            $parts = parse_url($str);
            $ret = $parts['path'];
            if (isset($parts['query']) && $parts['query'])
                $ret .= "?" . $parts['query'];
            return $ret;
        });
    }


    /** 
     * Removes "../" from urls.
     */
    public function url_remove_dot_dot($data) {
        return $this->process($data, function(string $str) : string {
            return str_replace("../", "/", $str);
        });
    }


    /** 
     * Returns one directory name from path.
     */
    public function dirname_one($data) {
        return $this->process($data, function(string $obj) : string {
            return basename(dirname($obj));
        });
    }


    /** 
     * Returns basename of path.
     */
    public function basename($data) {
        return $this->process($data, function(string $obj) : string {
            return basename($obj);
        });
    }
}
