<?php

/**
 * Base class for Writers.
 */
abstract class Writer extends Service {
    /**
     * This gets called after validating the Result object.
     * @param $result The result object, filled by the Reader and validated.
     */
    abstract public function write(Result $result);
}
