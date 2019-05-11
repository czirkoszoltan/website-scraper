<?php

class App {
    /** Files service. */
    public $files;
    
    /** Filters service. */
    public $filters;
    
    /** Logger service. */
    public $logger;
    
    
    /** Filename of imported file as specified on command line. */
    public $importfile;
    
    /** Import configuration. */
    public $config;
    
    
    public function __construct() {
        /* service init */
        $this->files = new Files($this);
        $this->filters = new Filters($this);
        $this->logger = new Logger($this);
    }
    
    
    /**
     * @param $configfile JSON configuration file to read.
     * @param $inputfile File to scrape data from.
     * @return Result object, but it is also written by the Writer.
     */
    public function import(string $configfile, string $inputfile) : Result {
        /* read and set import config */
        $configstr = $this->files->file_get_contents($configfile);
        $config = $this->files->json_decode($configstr);
        $this->config = $config;
        $this->inputfile = $inputfile;

        /* create import, writer and result objects */
        $readerclass = __namespace__ . "\\" . $config->reader;
        $writerclass = __namespace__ . "\\" . ($config->writer ?? 'Writer_Nop');
        $resultclass = __namespace__ . "\\" . ($config->result ?? 'Result');
        $reader = new $readerclass($this);
        $writer = new $writerclass($this);
        $result = new $resultclass;

        /* do the import */
        $inputdata = $this->files->file_get_contents($inputfile);
        $reader->read($inputdata, $result);
        $result->validate();
        $writer->write($result);
        
        return $result;
    }
}
