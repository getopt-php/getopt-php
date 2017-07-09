<?php

namespace GetOpt;

class Help
{
    /** @var string */
    protected $usageTemplate;

    /** @var string */
    protected $optionsTemplate;

    /**
     * Create a Help object
     *
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        $this->usageTemplate = __DIR__ . '/../resources/usage.php';
        $this->optionsTemplate = __DIR__ . '/../resources/options.php';
    }

    /**
     * Get the help text for $options
     *
     * @param string $scriptName
     * @param string $options
     * @param string $banner
     * @return string
     */
    public function render($scriptName, $options, $banner = null)
    {
        $rows = array();

        // we always append the usage
        $rows = array_merge($rows, include($this->usageTemplate));

        // when we have options we add them too
        if (!empty($options)) {
            $rows = array_merge($rows, include($this->optionsTemplate));
        }

        return implode(PHP_EOL, $rows) . PHP_EOL;
    }
}
