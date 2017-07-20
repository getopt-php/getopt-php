<?php

namespace GetOpt;

/**
 * Class Help
 *
 * @package GetOpt
 * @author  Thomas Flori <thflori@gmail.com>
 */
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
    public function __construct(array $settings = [])
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
     * @param array  $data Additional data for templates
     * @return string
     */
    public function render(Getopt $getopt, array $data = [])
    {
        $data['getopt'] = $getopt;

        if ($getopt->getCommand()) {
            $data['command'] = $getopt->getCommand();
        }

        // we always append the usage
        $helpText = $this->renderTemplate($this->usageTemplate, $data);

        // when we have options we add them too
        if ($getopt->hasOptions()) {
            $data['options'] = $getopt->getOptions(true);
            $helpText .= $this->renderTemplate($this->optionsTemplate, $data);
        }

        // when we have commands we render commands template
        if (!$getopt->getCommand() && $getopt->hasCommands()) {
            $data['commands'] = $getopt->getCommands();
            $helpText .= $this->renderTemplate($this->commandsTemplate, $data);
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
