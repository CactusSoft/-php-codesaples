<?php
 namespace Feed\Outputters;


/**
 * Class _BaseOutputter
 * @package Feed\Outputters
 *
 * Base feed data outputter class
 */
abstract class _BaseOutputter
{
    public $options = [];

    /**
     * Transform given feed data using formatter object and make an output
     *
     * @param string $data
     */
    final public function output(string $data)
    {
        $this->outputInternal($data);
    }

    /**
     * Make an output of processed and transformed feed data
     *
     * @param string $transformedData
     */
    abstract protected function outputInternal(string $transformedData);
}