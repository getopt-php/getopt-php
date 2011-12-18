<?php

/*
 * Copyright (c) 2011 Ulrich Schmidt-Goertz <ulrich at schmidt-goertz.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

/**
 * Getopt.PHP allows for easy processing of command-line arguments.
 * It is a more powerful, object-oriented alternative to PHP's built-in getopt() function.
 *
 * @version 1.0
 * @link    https://github.com/ulrichsg/getopt-php
 */
class Getopt {

    const NO_ARGUMENT = 0;
    const REQUIRED_ARGUMENT = 1;
    const OPTIONAL_ARGUMENT = 2;

    private $optionList = array();

    private $options;

    private $operands = array();

    /**
     * Create a new Getopt object.
     *
     * The argument $options can be either a string in the format accepted by the PHP library
     * function getopt() or an array
     *
     * @param mixed $options Array of options, a String, or null
     *
     * @link https://www.gnu.org/s/hello/manual/libc/Getopt.html GNU Getopt manual
     */
    public function __construct($options = null) {
        if ($options !== null) {
            $this->addOptions($options);
        }
    }

    /**
     * Evaluate the given arguments. These can be passed either as a string or as an array.
     * If nothing is passed, the running script's command line arguments are used.
     *
     * A {@link GetoptParseException} is thrown when the arguments are not well-formed or
     * do not conform to the options passed by the user.
     *
     * @param mixed $arguments optional
     *
     * @return void
     */
    public function parse($arguments = null) {
        $this->options = array();
        if (!isset($arguments)) {
            global $argv;
            $arguments = $argv;
            array_shift($arguments); // $argv[0] is the script's name
        } elseif (is_string($arguments)) {
            $arguments = explode(' ', $arguments);
        }

        $num_args = count($arguments);
        for ($i = 0; $i < $num_args; ++$i) {
            $arg = trim($arguments[$i]);
            if (empty($arg)) {
                continue;
            }
            if ($arg == '--' || substr($arg, 0, 1) != '-') {
                echo "fetching rest as operands\n";
                // no more options, treat the remaining arguments as operands
                $first_operand_index = $arg == '--' ? $i + 1 : $i;
                $this->operands = array_slice($arguments, $first_operand_index);
                break;
            }
            if (substr($arg, 0, 2) == '--') {
                // long option
                $option = substr($arg, 2);
                if (strpos($option, '=') === false) {
                    if ($i < $num_args - 1
                            && substr($arguments[$i + 1], 0, 1) != '-'
                            && $this->optionHasArgument($option, true)) {
                        $value = $arguments[$i + 1];
                        ++$i;
                    } else {
                        $value = null;
                    }
                } else {
                    list($option, $value) = explode('=', $option, 2);
                }
                $this->addOption($option, $value, true);
            } else {
                // short option
                $option = substr($arg, 1);
                if (strlen($option) > 1) {
                    // multiple options strung together
                    foreach (str_split($option, 1) as $ch) {
                        $this->addOption($ch, null, false);
                    }
                } else {
                    if ($i < $num_args - 1
                            && substr($arguments[$i + 1], 0, 1) != '-'
                            && $this->optionHasArgument($option, false)) {
                        $value = $arguments[$i + 1];
                        ++$i;
                    } else {
                        $value = null;
                    }
                    $this->addOption($option, $value, false);
                }
            }
        } // endfor
    }

    /**
     * Return the value of the given option. Must be invoked after parse().
     *
     * The return value can be any of the following:
     * <ul>
     *   <li><b>null</b> if the option is not given</li>
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
     * Return the list of operands. Must be invoked after parse().
     *
     * @return array
     */
    public function getOperands() {
        return $this->operands;
    }

    /**
     * Parse an option string.
     *
     * @param string $string the option string
     *
     * @return void
     * @internal
     */
    private function parseOptionString($string) {
        if (empty($string)) {
            throw new InvalidArgumentException('Option string must not be empty');
        }
        $option_list = array();
        $eol = strlen($string) - 1;
        $next_can_be_colon = false;
        for ($i = 0; $i <= $eol; ++$i) {
            $ch = $string[$i];
            if (!preg_match('/^[A-Za-z]$/', $ch)) {
                $colon = $next_can_be_colon ? " or ':'" : '';
                throw new InvalidArgumentException("Option string is not well formed: "
                        . "expected a letter$colon, found '$ch' at position " . ($i + 1));
            }
            if ($i == $eol || $string[$i + 1] != ':') {
                $option_list[] = array($ch, null, self::NO_ARGUMENT);
                $next_can_be_colon = true;
            } elseif ($i < $eol - 1 && $string[$i + 2] == ':') {
                $option_list[] = array($ch, null, self::OPTIONAL_ARGUMENT);
                $i += 2;
                $next_can_be_colon = false;
            } else {
                $option_list[] = array($ch, null, self::REQUIRED_ARGUMENT);
                ++$i;
                $next_can_be_colon = true;
            }
        }
        return $option_list;
    }

    /**
     * Check that the argument conforms to Getopt.PHP's option array rules.
     * Throws an exception on failure.
     *
     * @param array $options the option list
     *
     * @return array the validated options array
     * @internal
     */
    private function validateOptions(array $options) {
        $valid_argument_specs = array(
            self::NO_ARGUMENT, self::OPTIONAL_ARGUMENT, self::REQUIRED_ARGUMENT
        );
        if (empty($options)) {
            throw new InvalidArgumentException('No options given');
        }
        foreach ($options as $option) {
            if (!is_array($option) || count($option) < 3) {
                throw new InvalidArgumentException("Too few fields in argument, must be 3 (short/long/type)");
            }
            if (!(is_null($option[0]) || preg_match("/^[a-zA-Z]$/", $option[0]))) {
                throw new InvalidArgumentException("First component of option must be "
                        . "null or a letter, found '" . $option[0] . "'");
            }
            if (!(is_null($option[1]) || preg_match("/^[a-zA-Z0-9_-]*$/", $option[1]))) {
                throw new InvalidArgumentException("Second component of option must be "
                        . "null or an alphanumeric string, found '" . $option[1] . "'");
            }
            if (empty($option[0]) && empty($option[1])) {
                throw new InvalidArgumentException("The short and long name of an option must not both be empty");
            }
            if (!in_array($option[2], $valid_argument_specs, true)) {
                throw new InvalidArgumentException("Third component of option must be one of "
                        . "Getopt::NO_ARGUMENT, Getopt::OPTIONAL_ARGUMENT and Getopt::REQUIRED_ARGUMENT");
            }
        }
        return $options;
    }

    /**
     * Add an option to the list of known options.
     *
     * @param string $option the option's name
     * @param string $value the option's value (or null)
     * @param boolean $is_long whether the option name is long or short
     *
     * @return void
     * @internal
     */
    private function addOption($option, $value, $is_long) {
        foreach ($this->optionList as $opt) {
            if (($is_long && $opt[1] == $option) || (!$is_long && $opt[0] == $option)) {
                if ($opt[2] == self::REQUIRED_ARGUMENT && empty($value)) {
                    throw new UnexpectedValueException("Option '$option' must have a value");
                }
                // for no-argument options, check if they are duplicate
                if ($opt[2] == self::NO_ARGUMENT) {
                    $old_value = $this->getOption($option);
                    $value = is_null($old_value) ? 1 : $old_value + 1;
                }
                // for optional-argument options, set value to 1 if none was given
                if (empty($value)) {
                    $value = 1;
                }
                // add both long and short names (if they exist) to the option array to facilitate lookup
                if (!empty($opt[0])) {
                    $this->options[$opt[0]] = $value;
                }
                if (!empty($opt[1])) {
                    $this->options[$opt[1]] = $value;
                }
                return;
            }
        }
        throw new UnexpectedValueException("Option '$option' is unknown");
    }

    /**
     * Return true if the given option can take an argument, false if it can't or is unknown.
     *
     * @param string $name the option's name
     * @param boolean $is_long whether it is a long option
     *
     * @return boolean
     * @internal
     */
    private function optionHasArgument($name, $is_long) {
        foreach ($this->optionList as $option) {
            if ((!$is_long && $option[0] == $name)
                    || ($is_long && $option[1] == $name)) {
                return $option[2] != self::NO_ARGUMENT;
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
     * Parses and Adds options
     * The argument $options can be either a string in the format accepted by the PHP library
     * function getopt() or an array
     *
     * @param mixed $options Array of options, a String, or null
     */
    public function addOptions($options)
    {
        if (is_string($options)) {
            return $this->addParsedOptions($this->parseOptionString($options));
        }

        if (is_array($options)) {
            return $this->addParsedOptions($this->validateOptions($options));
        }
        
        throw new InvalidArgumentException("Getopt(): argument must be string or array");
    }

    /**
     * Merges new options with the ones already in the Getopt optionList.
     * 
     * @param array $options The array from parsing from parseOptionString() or validateOptions()
     *
     * @return array
     * @internal
     */
    private function addParsedOptions (array $options)
    {
        return $this->optionList = array_merge($this->optionList, $options);
    }
}