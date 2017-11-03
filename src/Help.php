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
    const TRANSLATION_USAGE    = 'translationUsage';
    const TRANSLATION_OPTIONS  = 'translationOptions';
    const TRANSLATION_OPERANDS = 'translationOperands';
    const TRANSLATION_COMMAND  = 'translationCommand';
    const TRANSLATION_COMMANDS = 'translationCommands';
    const TEMPLATE_USAGE       = 'usageTemplate';
    const TEMPLATE_OPTIONS     = 'optionsTemplate';
    const TEMPLATE_COMMANDS    = 'commandsTemplate';
    const DESCRIPTION          = 'description';
    const MAX_WIDTH            = 'maxWidth';

    /** @var string */
    protected $usageTemplate;

    /** @var string */
    protected $optionsTemplate;

    /** @var string */
    protected $commandsTemplate;

    /** @var array */
    protected $settings = [
        self::TRANSLATION_USAGE    => 'usage',
        self::TRANSLATION_OPTIONS  => 'options',
        self::TRANSLATION_OPERANDS => 'operands',
        self::TRANSLATION_COMMAND  => 'command',
        self::TRANSLATION_COMMANDS => 'commands',
        self::MAX_WIDTH            => 120,
    ];

    /** @var GetOpt */
    protected $getOpt;

    /** @var int */
    protected $screenWidth;

    /**
     * Create a Help object
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        foreach ($settings as $setting => $value) {
            $this->set($setting, $value);
        }
    }

    /**
     * Set $setting to $value
     *
     * @param string $setting
     * @param mixed $value
     * @return self
     */
    public function set($setting, $value)
    {
        switch ($setting) {
            case 'optionsTemplate':
            case 'commandsTemplate':
            case 'usageTemplate':
                call_user_func([$this, 'set' . ucfirst($setting)], $value);
                break;
            default:
                $this->settings[$setting] = $value;
                break;
        }

        return $this;
    }

    /**
     * Get the help text for $getopt
     *
     * @param GetOpt $getopt
     * @param array  $data Additional data for templates
     * @return string
     */
    public function render(GetOpt $getopt, array $data = [])
    {
        $this->getOpt = $getopt;
        foreach ($data as $setting => $value) {
            $this->set($setting, $value);
        }

        // we always append the usage
        if ($this->usageTemplate) {
            $data['getopt'] = $getopt;
            $data['command'] = $getopt->getCommand();
            $helpText = $this->renderTemplate($this->usageTemplate, $data);
        } else {
            $helpText = $this->renderUsage();
        }

        // when we have options we add them too
        if ($getopt->hasOptions()) {
            if ($this->optionsTemplate) {
                $data['options'] = $getopt->getOptionObjects();
                $helpText .= $this->renderTemplate($this->optionsTemplate, $data);
            } else {
                $helpText .= $this->renderOptions();
            }
        }

        // when we have commands we render commands template
        if (!$getopt->getCommand() && $getopt->hasCommands()) {
            if ($this->commandsTemplate) {
                $data['commands'] = $getopt->getCommands();
                $helpText         .= $this->renderTemplate($this->commandsTemplate, $data);
            } else {
                $helpText .= $this->renderCommands();
            }
        }

        return $helpText;
    }

    protected function renderUsage()
    {
        return  ucfirst($this->settings[self::TRANSLATION_USAGE]) . ': ' .
                $this->getOpt->get(GetOpt::SETTING_SCRIPT_NAME) . ' ' .
                $this->renderUsageCommand() .
                $this->renderUsageOptions() .
                $this->renderUsageOperands() . PHP_EOL .
                $this->renderDescription();
    }

    protected function renderOptions()
    {
        $text = PHP_EOL . ucfirst($this->settings[self::TRANSLATION_OPTIONS]) . ':' . PHP_EOL;

        $data            = [];
        $definitionWidth = 0;
        foreach ($this->getOpt->getOptionObjects() as $option) {
            $definition = implode(', ', array_filter([
                $option->short() ? '-' . $option->short() : null,
                $option->long() ? '--' . $option->long() : null,
            ]));

            if ($option->mode() !== GetOpt::NO_ARGUMENT) {
                $argument = '<' . $option->getArgument()->getName() . '>';
                if ($option->mode() === GetOpt::OPTIONAL_ARGUMENT) {
                    $argument = '[' . $argument . ']';
                }

                $definition .= ' ' . $argument;
            }

            if (strlen($definition) > $definitionWidth) {
                $definitionWidth = strlen($definition);
            }

            $data[] = [
                $definition,
                $option->description()
            ];
        }

        return $text . $this->renderColumns($definitionWidth, $data);
    }

    protected function renderCommands()
    {
        $text = PHP_EOL . ucfirst($this->settings[self::TRANSLATION_COMMANDS]) . ':' . PHP_EOL;

        $data      = [];
        $nameWidth = 0;
        foreach ($this->getOpt->getCommands() as $command) {
            if (strlen($command->name()) > $nameWidth) {
                $nameWidth = strlen($command->name());
            }

            $data[] = [
                $command->name(),
                $command->shortDescription()
            ];
        }

        return $text . $this->renderColumns($nameWidth, $data);
    }

    protected function renderUsageCommand()
    {
        if ($command = $this->getOpt->getCommand()) {
            return $command->name() . ' ';
        } elseif ($this->getOpt->hasCommands()) {
            return '<' . $this->settings[self::TRANSLATION_COMMAND] . '> ';
        }

        return '';
    }
    
    protected function renderUsageOptions()
    {
        if ($this->getOpt->hasOptions() || !$this->getOpt->get(GetOpt::SETTING_STRICT_OPTIONS)) {
            return '[' . $this->settings[self::TRANSLATION_OPTIONS] . '] ';
        }
    }
    
    protected function renderUsageOperands()
    {
        $usage = '';
        
        $lastOperandMultiple = false;
        if ($this->getOpt->hasOperands()) {
            foreach ($this->getOpt->getOperandObjects() as $operand) {
                $name = '<' . $operand->getName() . '>';
                if (!$operand->isRequired()) {
                    $name = '[' . $name . ']';
                }
                $usage .= $name . ' ';
                if ($operand->isMultiple()) {
                    $usage .= '[<' . $operand->getName() . '>...]';
                    $lastOperandMultiple = true;
                }
            }
        }

        if (!$lastOperandMultiple && !$this->getOpt->get(GetOpt::SETTING_STRICT_OPERANDS)) {
            $usage .= '[' . $this->settings[self::TRANSLATION_OPERANDS] . ']';
        }
        
        return $usage;
    }

    protected function renderDescription()
    {
        if ($command = $this->getOpt->getCommand()) {
            return PHP_EOL . $command->description() . PHP_EOL . PHP_EOL;
        } elseif (isset($this->settings[self::DESCRIPTION])) {
            return PHP_EOL . $this->settings[self::DESCRIPTION] . PHP_EOL . PHP_EOL;
        }

        return '';
    }

    protected function getScreenWidth()
    {
        if (!$this->screenWidth) {
            $columns = defined('COLUMNS') ? (int)COLUMNS : (int)@getenv('COLUMNS');
            if (empty($columns)) {
                $process = proc_open('tput cols', [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ], $pipes);
                $columns = (int)stream_get_contents($pipes[1]);
                proc_close($process);
            }

            $screenWidth = !empty($columns) ? $columns: 90;
            $this->screenWidth = min([ $this->settings[self::MAX_WIDTH], $screenWidth ]);
        }

        return $this->screenWidth;
    }

    protected function renderColumns($columnWidth, $data)
    {
        $text = '';
        $screenWidth = $this->getScreenWidth();

        foreach ($data as $dataRow) {
            $row = sprintf('  % -' . $columnWidth . 's  %s', $dataRow[0], $dataRow[1]);

            while (mb_strlen($row) > $screenWidth) {
                $p = strrpos(substr($row, 0, $screenWidth), ' ');
                $text .= substr($row, 0, $p) . PHP_EOL;
                $row = sprintf('  %s  %s', str_repeat(' ', $columnWidth), substr($row, $p+1));
            }

            $text .= $row . PHP_EOL;
        }

        return $text;
    }

    protected function renderTemplate($template, $data)
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include($template);
        return ob_get_clean();
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
     * @return $this
     */
    public function setUsageTemplate($usageTemplate)
    {
        $this->usageTemplate = $usageTemplate;
        return $this;
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
     * @return $this
     */
    public function setOptionsTemplate($optionsTemplate)
    {
        $this->optionsTemplate = $optionsTemplate;
        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore trivial
     */
    public function getCommandsTemplate()
    {
        return $this->commandsTemplate;
    }

    /**
     * @param string $commandsTemplate
     * @codeCoverageIgnore trivial
     * @return $this
     */
    public function setCommandsTemplate($commandsTemplate)
    {
        $this->commandsTemplate = $commandsTemplate;
        return $this;
    }
}
