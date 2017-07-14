<?php

namespace GetOpt;

class Help implements HelpInterface
{
    /** @var string */
    protected $usageTemplate;

    /** @var string */
    protected $optionsTemplate;

    /** @var string */
    protected $commandsTemplate;

    /**
     * Create a Help object
     *
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        $this->usageTemplate = __DIR__ . '/../resources/usage.php';
        $this->optionsTemplate = __DIR__ . '/../resources/options.php';
        $this->commandsTemplate = __DIR__ . '/../resources/commands.php';
    }

    /**
     * @return string
     * @codeCoverageIgnore trivial
     */
    public function getUsageTemplate()
    {
        return $this->usageTemplate;
    }

    /**
     * @param string $usageTemplate
     * @codeCoverageIgnore trivial
     */
    public function setUsageTemplate($usageTemplate)
    {
        $this->usageTemplate = $usageTemplate;
    }

    /**
     * @return string
     * @codeCoverageIgnore trivial
     */
    public function getOptionsTemplate()
    {
        return $this->optionsTemplate;
    }

    /**
     * @param string $optionsTemplate
     * @codeCoverageIgnore trivial
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
        if ($getopt->hasOptions()) {
            $helpText .= $this->renderTemplate($this->optionsTemplate, array(
                'options' => $getopt->getOptions(true)
            ));
        }

        // when we have commands we render commands template
        if (!$getopt->getCommand() && $getopt->hasCommands()) {
            $helpText .= $this->renderTemplate($this->commandsTemplate, array(
                'commands' => $getopt->getCommands()
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
