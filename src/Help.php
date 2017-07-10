<?php

namespace GetOpt;

class Help implements HelpInterface
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
     * @return string
     */
    public function getUsageTemplate()
    {
        return $this->usageTemplate;
    }

    /**
     * @param string $usageTemplate
     */
    public function setUsageTemplate($usageTemplate)
    {
        $this->usageTemplate = $usageTemplate;
    }

    /**
     * @return string
     */
    public function getOptionsTemplate()
    {
        return $this->optionsTemplate;
    }

    /**
     * @param string $optionsTemplate
     */
    public function setOptionsTemplate($optionsTemplate)
    {
        $this->optionsTemplate = $optionsTemplate;
    }

    /**
     * Get the help text for $options
     *
     * @param Getopt $getopt
     * @return string
     */
    public function render(Getopt $getopt)
    {
        // we always append the usage
        $helpText = $this->renderTemplate($this->usageTemplate, array('getopt' => $getopt));

        // when we have options we add them too
        $options = $getopt->getOptions(true);
        if (!empty($options)) {
            $helpText .= $this->renderTemplate($this->optionsTemplate, array(
                'getopt' => $getopt,
                'options' => $options
            ));
        }

        return $helpText;
    }

    protected function renderTemplate($template, $data)
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include($template);
        return ob_get_clean();
    }
}
