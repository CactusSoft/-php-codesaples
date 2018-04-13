<?php


namespace Feed\Outputters;

/**
 * Class DownloadOutputter
 * @package Feed\Outputters
 *
 * Download feed data outputter
 * Available options:
 *  - fileName (string) - a name of the file to be downloaded, the value is used in the Content-Disposition header
 *  - contentType (string) - a value of the Content Type header
 */
class DownloadOutputter extends _BaseOutputter
{
    /**
     * @inheritdoc
     */
    protected function outputInternal(string $transformedData)
    {
        $fileName = $this->options['fileName'] ?? 'feed.txt';
        $contentType = $this->options['contentType'] ?? 'text/plain';

        header(sprintf('Content-Type: %s; charset=UTF-8', $contentType));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $fileName));
        echo $transformedData;
    }
}