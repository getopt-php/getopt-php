<?php

namespace GetOpt;

class Help
{
    /** @var string */
    protected $usageTemplate = __DIR__ . '/../resources/usage.php';

    /** @var string */
    protected $optionTemplate = __DIR__ . '/../resources/option.php';

    /** @var int */
    protected $padding = 25;

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
