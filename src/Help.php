<?php

namespace GetOpt;

class Help
{
    /** @var string */
    protected $usageTemplate;

    /** @var string */
    protected $optionTemplate;

    /** @var int */
    protected $padding = 25;

    /**
     * Create a Help object
     *
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        $this->usageTemplate = __DIR__ . '/../resources/usage.php';
        $this->optionTemplate = __DIR__ . '/../resources/option.php';
    }

    /**
     * Get the help text for $options
     *
     * @param string $scriptName
     * @param string $options
     * @param string $banner
     * @param string $padding
     * @return string
     */
    public function getText($scriptName, $options, $banner = null, $padding = null)
    {
        $padding = $padding ?: $this->padding;
        $usage = trim(include($this->usageTemplate));
        $rows = array($usage);

        if (!empty($options)) {
            $rows[] = 'Options:';
            foreach ($options as $option) {
                $rows[] = include($this->optionTemplate);
            }
        }

        return implode(PHP_EOL, $rows) . PHP_EOL;
    }
}
