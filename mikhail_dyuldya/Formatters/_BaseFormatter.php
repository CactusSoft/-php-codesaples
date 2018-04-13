<?php

namespace Feed\Formatters;

/**
 * Class _BaseFormatter
 * @package Feed\Formatters
 *
 * Base feed data formatter
 * Used to transform given array of data to needed format
 */
abstract class _BaseFormatter
{
    /**
     * @var array $options An array of options used during the transformation
     */
    public $options = [];

    /**
     * Transform given data to specific format and return the result string
     *
     * @param array $feedData
     * @return string
     */
    abstract public function process(array $feedData) : string;
}