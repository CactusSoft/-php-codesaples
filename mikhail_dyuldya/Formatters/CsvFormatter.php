<?php


namespace Feed\Formatters;

/**
 * Class CsvFormatter
 * @package Feed\Formatters
 *
 * CSV feed data formatter
 *
 * Available options:
 *  - separator - A csv separator char
 *  - includeColumnNames - A flag tells whether column names should be included or not
 */
class CsvFormatter extends _BaseFormatter
{
    /**
     * @inheritdoc
     */
    public function process(array $feedData) : string
    {
        $separator = $this->options['separator'] ?? ',';
        $includeColumnNames = !empty($this->options['includeColumnNames']);

        $resource = fopen('php://temp', 'w+');

        if ($includeColumnNames) {
            $columnNames = array_keys(reset($feedData));
            fputcsv($resource, $columnNames, $separator);
        }

        foreach ($feedData as $oneDataItem) {
            fputcsv($resource, $oneDataItem, $separator);
        }

        rewind($resource);
        $csvString = stream_get_contents($resource);
        fclose($resource);
        return $csvString;
    }
}