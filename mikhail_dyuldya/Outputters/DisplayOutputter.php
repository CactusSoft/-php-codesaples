<?php


namespace Feed\Outputters;

/**
 * Class DisplayOutputter
 * @package Feed\Outputters
 *
 * Display feed data outputter
 *
 * Available options:
 *  - contentType (string) - a value of the Content Type header
 */
class DisplayOutputter extends _BaseOutputter
{
    /**
     * @inheritdoc
     */
    public function outputInternal(string $transformedData)
    {
        $contentType = $this->options['contentType'] ?? 'text/plain';

        header(sprintf('Content-Type: %s; charset=UTF-8', $contentType));
        echo $transformedData;
    }
}