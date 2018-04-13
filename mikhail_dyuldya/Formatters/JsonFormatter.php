<?php


namespace Feed\Formatters;

/**
 * Class JsonFormatter
 * @package Feed\Formatters
 *
 * Json feed data formatter
 */
class JsonFormatter extends _BaseFormatter
{
    /**
     * @inheritdoc
     */
    public function process(array $feedData) : string
    {
        return json_encode($feedData);
    }
}