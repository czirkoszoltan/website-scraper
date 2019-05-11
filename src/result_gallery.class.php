<?php

/**
 * Example derived class of Result:
 * image gallery with name, images and captions.
 */
class Result_Gallery extends Result {
    public $name = "";
    
    public $images = [];
    
    public $capions = [];
    
    public function validate() {
        $this->check(is_string($this->name) && $this->name != "", "Gallery name cannot be empty");
        $this->check(is_array($this->images) && $this->images != [], "Gallery cannot be empty");
        $this->check(is_array($this->capions), "Image captions must be an array");
        $this->check(count($this->images) == count($this->capions), "Equal number of images and captions required");
    }
}
