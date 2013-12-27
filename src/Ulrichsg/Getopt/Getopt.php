<?php

namespace Ulrichsg\Getopt;

/**
 * Getopt.PHP allows for easy processing of command-line arguments.
 * It is a more powerful, object-oriented alternative to PHP's built-in getopt() function.
 *
 * @version 2.0.0-alpha
 * @license MIT
 * @link    https://github.com/ulrichsg/getopt-php
 */
class Getopt implements \Countable, \ArrayAccess, \IteratorAggregate {

    const NO_ARGUMENT = 0;
    const REQUIRED_ARGUMENT = 1;
    const OPTIONAL_ARGUMENT = 2;

    /** @var OptionParser */
    protected $optionParser;

    /** @var string */
    protected $scriptName;
    /** @var Option[] */
    protected $optionList = array();
    /** @var array */
    protected $options = array();
    /** @var array */
    protected $operands = array();

    /**
     * Create a new Getopt object.
     *
     * The argument $options can be either a string in the format accepted by the PHP library
     * function getopt() or an array
     *
     * @param mixed $options Array of options, a String, or null
     * @param int $defaultType The default option type to use when omitted
     * @throws \InvalidArgumentException
     *
     * @link https://www.gnu.org/s/hello/manual/libc/Getopt.html GNU Getopt manual
     */
    public function __construct($options = null, $defaultType = Getopt::NO_ARGUMENT) {
        $this->optionParser = new OptionParser($defaultType);
        if ($options !== null) {
            $this->addOptions($options);
        }
    }

    /**
     * Parses and Adds options
     * The argument $options can be either a string in the format accepted by the PHP library
     * function getopt() or an array
     *
     * @param mixed $options Array of options, a String, or null
     * @return Option[]
     * @throws \InvalidArgumentException
     */
    public function addOptions($options)
    {
        if (is_string($options)) {
            return $this->mergeOptions($this->optionParser->parseString($options));
        }
        if (is_array($options)) {
            return $this->mergeOptions($this->optionParser->parseArray($options));
        }
        throw new \InvalidArgumentException("Getopt(): argument must be string or array");
    }

    /**
     * Merges new options with the ones already in the Getopt optionList.
     *
     * @param Option[] $options The array from parsing from parseOptionString() or validateOptions()
     * @return Option[]
     */
    protected function mergeOptions(array $options)
    {
        $mergedList = array_merge($this->optionList, $options);
        return $this->optionList = $mergedList;
    }

    /**
     * Evaluate the given arguments. These can be passed either as a string or as an array.
     * If nothing is passed, the running script's command line arguments are used.
     *
     * A {@link \UnexpectedValueException} or {@link \InvalidArgumentException} is thrown
     * when the arguments are not well-formed or do not conform to the options passed by the user.
     *
     * @param mixed $arguments optional ARGV array or space separated string
     *
     * @return void
     */
    public function parse($arguments = null)
    {
        $this->options = array();
        if (!isset($arguments)) {
            global $argv;
            $arguments = $argv;
            $this->scriptName = array_shift($arguments); // $argv[0] is the script's name
        } elseif (is_string($arguments)) {
            $this->scriptName = $_SERVER['PHP_SELF'];
            $arguments = explode(' ', $arguments);
        }

        $parser = new CommandLineParser($this->optionList);
        $parser->parse($arguments);
        $this->options = $parser->getOptions();
        $this->operands = $parser->getOperands();
    }

    /**
     * Return the value of the given option. Must be invoked after parse().
     *
     * The return value can be any of the following:
     * <ul>
     *   <li><b>null</b> if the option is not given and does not have a default value</li>
	 *   <li><b>the default value</b> if it has been defined and the option is not given</li>
     *   <li><b>an integer</b> if the option is given without argument. The
     *       returned value is the number of occurrences of the option.</li>
     *   <li><b>a string</b> if the option is given with an argument. The returned value is that argument.</li>
     * </ul>
     *
     * @param string $name The (short or long) option.
     *
     * @return mixed
     */
    public function getOption($name) {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Return all the options. Must be invoked after parse().
     *
     * Will return an empty array if called before parse() is called.
     *
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * Return the list of operands. Must be invoked after parse().
     *
     * @return array
     */
    public function getOperands() {
        return $this->operands;
    }

    /**
     * Return a specific operand (does not do bounds checking). Must be invoked after parse().
     *
     * @param int $index
     * @return string
     */
    public function getOperand($index) {
        return $this->operands[$index];
    }

    /**
     * Returns true or false depending on if any operands were passed. Must be invoked after parse().
     *
     * @return boolean
     */
    public function hasOperands() {
        return $this->getOperandCount() > 0;
    }

    /**
     * Returns number of operands, if any, were passed. Must be invoked after parse().
     *
     * @return integer
     */
    public function getOperandCount() {
        return count($this->getOperands());
    }

    /**
     * @param int $padding Number of characters to pad output of options to
     *
     * @return string help message for given options.
     */
    public function getHelpText($padding = 25) {
        $helpText = sprintf("Usage: %s [options] [operands]\n", $this->scriptName);
        $helpText .= "Options:\n";
        foreach ($this->optionList as $option) {
            $mode = '';
            switch ($option->mode()) {
                case self::NO_ARGUMENT: $mode = ''; break;
                case self::REQUIRED_ARGUMENT: $mode = "<arg>"; break;
                case self::OPTIONAL_ARGUMENT: $mode = "[<arg>]"; break;
            }
            $short = ($option->short()) ? '-'.$option->short() : '';
            $long = ($option->long()) ? '--'.$option->long() : '';
            if ($short && $long) {
                $options = $short.', '.$long;
            } else {
                $options = $short ?: $long;
            }
            $padded = str_pad(sprintf("  %s %s", $options, $mode), $padding);
            $helpText .= sprintf("%s %s\n", $padded, $option->getDescription());
        }
        return $helpText;
    }


    /*
     * Interface support functions
     */
	
	public function count() {
		return count($this->options);
	}

	public function offsetExists($offset) {
		return isset($this->options[$offset]);
	}

	public function offsetGet($offset) {
		return $this->getOption($offset);
	}

	public function offsetSet($offset, $value) {
		throw new \LogicException('Getopt is read-only');
	}

	public function offsetUnset($offset) {
		throw new \LogicException('Getopt is read-only');
	}

	public function getIterator() {
		// For options that have both short and long names, $this->options has two entries.
		// We don't want this when iterating, so we have to filter the duplicates out.
		$filteredOptions = array();
		foreach ($this->options as $name => $value) {
			$keep = true;
			foreach ($this->optionList as $option) {
				if ($option->long() == $name && !is_null($option->short())) {
					$keep = false;
				}
			}
			if ($keep) {
				$filteredOptions[$name] = $value;
			}
		}
		return new \ArrayIterator($filteredOptions);
	}
}
