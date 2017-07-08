<?php

namespace GetOpt;

class Getopt implements \Countable, \ArrayAccess, \IteratorAggregate
{
    const NO_ARGUMENT = 0;
    const REQUIRED_ARGUMENT = 1;
    const OPTIONAL_ARGUMENT = 2;
    const MULTIPLE_ARGUMENT = 3;

    const SETTING_SCRIPT_NAME  = 'scriptName';
    const SETTING_DEFAULT_MODE = 'defaultMode';
    const SETTING_BANNER       = 'banner';

    /** @var OptionParser */
    protected $optionParser;

    /** @var Help */
    protected $help;

    /** @var array */
    protected $settings = [
        self::SETTING_DEFAULT_MODE => self::NO_ARGUMENT
    ];

    /**@var Option[] */
    protected $options = array();

    /** @var Option[] */
    protected $optionMapping = array();

    /** @var string[] */
    protected $operands = array();

    /**
     * Creates a new Getopt object.
     *
     * The argument $options can be either a string in the format accepted by the PHP library
     * function getopt() or an array.
     *
     * @param array $settings
     * @link https://www.gnu.org/s/hello/manual/libc/Getopt.html GNU Getopt manual
     */
    public function __construct($options = null, array $settings = array())
    {
        if ($options !== null) {
            $this->addOptions($options);
        }

        $this->set(
            self::SETTING_SCRIPT_NAME,
            isset($_SERVER['argv'][0]) ? $_SERVER['argv'][0] : (
                isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null
            )
        );
        foreach ($settings as $setting => $value) {
            $this->set($setting, $value);
        }
    }

    public function set($setting, $value)
    {
        $this->settings[$setting] = $value;
//        switch ($setting) {
//            default:
//
//        }
        return $this;
    }

    public function get($setting)
    {
        return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
    }

    public function addOptions($options)
    {
        if (is_string($options)) {
            $options = $this->getOptionParser()->parseString($options);
        }

        if (!is_array($options)) {
            throw new \InvalidArgumentException('Getopt(): argument must be string or array');
        }

        foreach ($options as $option) {
            $this->addOption($option);
        }

        return $this;
    }

    public function addOption($option)
    {
        if (!$option instanceof Option) {
            if (is_string($option)) {
                $options = $this->getOptionParser()->parseString($option);
                // this is addOption - so we use only the first one
                $option = $options[0];
            } elseif (is_array($option)) {
                $option = $this->getOptionParser()->parseArray($option);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    '$option has to be a string, an array or an Option. %s given',
                    gettype($option)
                ));
            }
        }

        if ($this->getOption($option->short(), true) || $this->getOption($option->long(), true)) {
            throw new \InvalidArgumentException('$option`s short and long name have to be unique');
        }

        $this->options[] = $option;

        return $this;
    }

    /**
     * @param array|string|Arguments $arguments
     */
    public function process($arguments = null)
    {
        if ($arguments === null) {
            $arguments = isset($_SERVER['argv']) ? array_slice($_SERVER['argv'], 1) : array();
            $arguments = new Arguments($arguments);
        } elseif (is_array($arguments)) {
            $arguments = new Arguments($arguments);
        } elseif (is_string($arguments)) {
            $arguments = Arguments::fromString($arguments);
        } elseif (!$arguments instanceof Arguments) {
            throw new \InvalidArgumentException(
                '$arguments has to be an instance of Arguments, an arguments string, an array of arguments or null'
            );
        }

        $arguments->process($this, function ($operand) {
            $this->operands[] = $operand;
        });
    }

    /**
     * Returns an usage information text generated from the given options.
     * @param int $padding Number of characters to pad output of options to
     * @return string
     */
    public function getHelpText($padding = 25)
    {
        return $this->getHelp()->getText(
            $this->get(self::SETTING_SCRIPT_NAME),
            $this->options,
            $this->get(self::SETTING_BANNER),
            $padding
        );
    }

    /**
     * Get a option by $name
     *
     * @param string $name Short or long name of the option
     * @return Option|mixed
     */
    public function getOption($name, $object = false)
    {
        if (!isset($this->optionMapping[$name])) {
            $this->optionMapping[$name] = null;
            foreach ($this->options as $option) {
                if ($option->matches($name)) {
                    $this->optionMapping[$name] = $option;
                    break;
                }
            }
        }

        if ($object) {
            return $this->optionMapping[$name];
        }

        return $this->optionMapping[$name] !== null ? $this->optionMapping[$name]->getValue() : null;
    }

    public function getHelp()
    {
        if (!$this->help) {
            $this->help = new Help();
        }

        return $this->help;
    }

    /**
     * Returns the list of options. Must be invoked after parse() (otherwise it returns an empty array).
     *
     * @return array
     */
    public function getOptions()
    {
        $result = [];

        foreach ($this->options as $option) {
            $value = $option->getValue();
            if ($value !== null) {
                if ($short = $option->short()) {
                    $result[$short] = $value;
                }
                if ($long = $option->long()) {
                    $result[$long] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Returns the list of operands. Must be invoked after parse().
     *
     * @return array
     */
    public function getOperands()
    {
        return $this->operands;
    }

    /**
     * Returns the i-th operand (starting with 0), or null if it does not exist. Must be invoked after parse().
     *
     * @param int $i
     * @return string
     */
    public function getOperand($i)
    {
        return ($i < count($this->operands)) ? $this->operands[$i] : null;
    }

    /**
     * Create or get the OptionParser
     *
     * @return OptionParser
     */
    protected function getOptionParser()
    {
        if ($this->optionParser === null) {
            $this->optionParser = new OptionParser($this->settings[self::SETTING_DEFAULT_MODE]);
        }

        return $this->optionParser;
    }

    // backward compatibility

    /**
     * Set script name manually
     *
     * @param string $scriptName
     * @return Getopt
     * @deprecated Use `Getopt::set(Getopt::SETTING_SCRIPT_NAME, $scriptName)` instead
     * @codeCoverageIgnore
     */
    public function setScriptName($scriptName)
    {
        return $this->set(self::SETTING_SCRIPT_NAME, $scriptName);
    }

    /**
     * Process $arguments or $_SERVER['argv']
     *
     * These function is an alias for process now. Parse was not the correct verb for what
     * the function is currently doing.
     *
     * @deprecated Use `Getopt::process($arguments)` instead
     * @param mixed $arguments optional ARGV array or argument string
     * @codeCoverageIgnore
     */
    public function parse($arguments = null)
    {
        $this->process($arguments);
    }

    /**
     * Get the current banner if defined
     *
     * @return string
     * @deprecated Use `Help` for formatting the help message
     */
    public function getBanner()
    {
        return $this->get(self::SETTING_BANNER);
    }

    /**
     * Set the banner
     *
     * @param string $banner
     * @return Getopt
     * @deprecated Use `Help` for formatting the help message
     */
    public function setBanner($banner)
    {
        return $this->set(self::SETTING_BANNER, $banner);
    }

    // array functions

    public function getIterator()
    {
        $result = [];

        foreach ($this->options as $option) {
            if ($value = $option->getValue()) {
                $name = $option->short() ?: $option->long();
                $result[$name] = $value;
            }
        }

        return new \ArrayIterator($result);
    }

    public function offsetExists($offset)
    {
        $option = $this->getOption($offset, true);
        return $option && $option->getValue() !== null;
    }

    public function offsetGet($offset)
    {
        $option = $this->getOption($offset, true);
        return $option ? $option->getValue() : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Read only array access');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Read only array access');
    }

    public function count()
    {
        return $this->getIterator()->count();
    }
}
