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
     * @throws \InvalidArgumentException
     * @return array
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
     * @param array $options The array from parsing from parseOptionString() or validateOptions()
     * @return array
     * @internal
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
    public function parse($arguments = null) {
        $this->options = array();
        if (!isset($arguments)) {
            global $argv;
            $arguments = $argv;
            $this->scriptName = array_shift($arguments); // $argv[0] is the script's name
        } elseif (is_string($arguments)) {
            $this->scriptName = $_SERVER['PHP_SELF'];
            $arguments = explode(' ', $arguments);
        }

        $num_args = count($arguments);
        for ($i = 0; $i < $num_args; ++$i) {
            $arg = trim($arguments[$i]);
            if (empty($arg)) {
                continue;
            }
            if ($arg == '--' || mb_substr($arg, 0, 1) != '-') {
                // no more options, treat the remaining arguments as operands
                $first_operand_index = $arg == '--' ? $i + 1 : $i;
                $this->operands = array_slice($arguments, $first_operand_index);
                break;
            }
            if (mb_substr($arg, 0, 2) == '--') {
                // long option
                $option = mb_substr($arg, 2);
                if (strpos($option, '=') === false) {
                    if ($i < $num_args - 1
                            && mb_substr($arguments[$i + 1], 0, 1) != '-'
                            && $this->optionHasArgument($option)) {
                        $value = $arguments[$i + 1];
                        ++$i;
                    } else {
                        $value = null;
                    }
                } else {
                    list($option, $value) = explode('=', $option, 2);
                }
                $this->addOption($option, $value);
            } else {
                // short option
                $option = mb_substr($arg, 1);
                if (mb_strlen($option) > 1) {
                    // multiple options strung together
                    $options = $this->mb_str_split($option, 1);
                    foreach ($options as $j => $ch) {
                        if ($j < count($options) - 1
                            || !(
                                $i < $num_args - 1
                                && mb_substr($arguments[$i + 1], 0, 1) != '-'
                                && $this->optionHasArgument($ch)
                            )
                        ) {
                            $this->addOption($ch, null);
                        } else {    // e.g. `ls -sw 100`
                            $value = $arguments[$i + 1];
                            ++$i;
                            $this->addOption($ch, $value);
                        }
                    }
                } else {
                    if ($i < $num_args - 1
                            && mb_substr($arguments[$i + 1], 0, 1) != '-'
                            && $this->optionHasArgument($option)) {
                        $value = $arguments[$i + 1];
                        ++$i;
                    } else {
                        $value = null;
                    }
                    $this->addOption($option, $value);
                }
            }
        } // endfor

		$this->addDefaultValues();

        // remove '--' from operands array
        $operands = array();
        foreach($this->operands as $operand) {
            if ($operand !== '--') {
                $operands[] = $operand;
            }
        }
        $this->operands = $operands;
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
     *   <li><b>a string</b> if the option is given with an argument. The
     *       returned value is that argument.</li>
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
     * Add an option to the list of known options.
     *
     * @param string $string the option's name
     * @param string $value the option's value (or null)
     * @throws \UnexpectedValueException
     * @return void
     * @internal
     */
    protected function addOption($string, $value) {
        foreach ($this->optionList as $option) {
            if ($option->matches($string)) {
                if ($option->mode() == self::REQUIRED_ARGUMENT && !mb_strlen($value)) {
                    throw new \UnexpectedValueException("Option '$string' must have a value");
                }
                // for no-argument options, check if they are duplicate
                if ($option->mode() == self::NO_ARGUMENT) {
                    $oldValue = $this->getOption($string);
                    $value = is_null($oldValue) ? 1 : $oldValue + 1;
                }
                // for optional-argument options, set value to 1 if none was given
                $value = (mb_strlen($value) > 0) ? $value : 1;
                // add both long and short names (if they exist) to the option array to facilitate lookup
                if ($option->short()) {
                    $this->options[$option->short()] = $value;
                }
                if ($option->long()) {
                    $this->options[$option->long()] = $value;
                }
                return;
            }
        }
        throw new \UnexpectedValueException("Option '$string' is unknown");
    }

    /**
	 * If there are options with default values that were not overridden by the parsed option string,
	 * add them to the list of known options.
	 *
	 * @internal
	 */
	protected function addDefaultValues() {
		foreach ($this->optionList as $option) {
			if ($option->hasDefaultValue()
					&& is_null($this->getOption($option->short()))
					&& is_null($this->getOption($option->long()))) {
				if ($option->short()) {
					$this->addOption($option->short(), $option->getDefaultValue());
				}
				if ($option->long()) {
					$this->addOption($option->long(), $option->getDefaultValue());
				}
			}
		}
	}

    /**
     * Return true if the given option can take an argument, false if it can't or is unknown.
     *
     * @param string $name the option's name
     * @return boolean
     * @internal
     */
    protected function optionHasArgument($name) {
        foreach ($this->optionList as $option) {
            if ($option->matches($name)) {
                return $option->mode() != self::NO_ARGUMENT;
            }
        }
        return false;
    }

    /**
     * Return the option list used by this Getopt.
     *
     * This function is used for testing. It is not meant for production use.
     *
     * @return array
     * @internal
     */
    public function getOptionList() {
        return $this->optionList;
    }

    /**
     * @param string $str string to split
     * @param int $l
     *
     * @return array
     * @internal
     */
    protected function mb_str_split($str, $l = 0)
    {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }

            return $ret;
        }

        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
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
