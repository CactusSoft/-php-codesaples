<?php

namespace Feed;

use Feed\Formatters\_BaseFormatter;
use Feed\Outputters\_BaseOutputter;

/**
 * Class DefaultFeed
 * @package Feed
 *
 * Handles feed's data processing and outputting
 */
class DefaultFeed
{
    protected $settings = [];
    protected $rawData = [];
    protected $processedData = [];

    /** @var _BaseOutputter $outputter*/
    protected $outputter;
    /** @var _BaseFormatter $formatter*/
    protected $formatter;

    /**
     * DefaultFeed constructor.
     * @param array $settings A multidimensional array of feed's parameters options
     *
     * Format:
     * [
     *   'key' => [
     *      'name' => string (optional, if not specified the key will be used as parameter name),
     *      'value' => callback (optional, if not specified the key will be used to access the value of each feed item)
     *    ],
     *    ...
     * ]
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Set raw source data we generate the feed from
     *
     * @param array $data
     * @return $this
     */
    public function setRawData(array $data)
    {
        $this->rawData = $data;
        return $this;
    }

    /**
     * Set feed outputter object
     *
     * @param _BaseOutputter $outputter
     * @return $this
     */
    public function setOutputter(_BaseOutputter $outputter)
    {
        $this->outputter = $outputter;
        return $this;
    }

    /**
     * Set feed data formatted object
     *
     * @param _BaseFormatter $formatter
     * @return $this
     */
    public function setFormatter(_BaseFormatter $formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Process and output the feed's data
     *
     * @throws \FeedException
     */
    public function output()
    {
        if (!$this->outputter) {
            throw new \FeedException('No outputter specified');
        }

        if (!$this->formatter) {
            throw new \FeedException('No formatter specified');
        }

        $this->process();
        $this->outputter->output($this->formatter->process($this->processedData));
    }

    /**
     * Process raw source data using specified settings
     */
    protected function process()
    {
        foreach ($this->rawData as $oneFeedItem) {
            $this->processedData[] = $this->processSingleDataItem($oneFeedItem);
        }
    }

    /**
     * Process single data item based on feed's options
     *
     * @param array $item
     * @return array
     */
    protected function processSingleDataItem(array $item)
    {
        $tmpItem = [];

        foreach ($this->settings as $oneColumn => $columnOptions) {
            $label = $columnOptions['label'] ?? $oneColumn;

            //Use callback to get the parameter value
            if (isset($columnOptions['value']) && is_callable($columnOptions['value'])) {
                $callback = $columnOptions['value'];
                $value = $callback($item);
            } else {
                //Use column key to access the raw item value
                $value = $item[$oneColumn] ?? null;
            }

            $tmpItem[$label] = $value;
        }

        return $tmpItem;
    }
}