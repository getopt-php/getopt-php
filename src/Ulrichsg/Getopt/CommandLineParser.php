<?php

namespace Ulrichsg\Getopt;

use Ulrichsg\Getopt\Util\String;

/**
 * Parses command line arguments according to a list of allowed options.
 */
class CommandLineParser
{
    /** @var Option[] */
    private $optionList;

    /**
     * Creates a new instance.
     *
     * @param Option[] $optionList the list of allowed options
     */
    public function __construct(array $optionList)
    {
        $this->optionList = $optionList;
    }

    /**
     * Parses the given arguments and converts them into options and operands.
     *
     * @param mixed $arguments a string or an array with one argument per element
     * @return Result
     */
    public function parse($arguments)
    {
        if (!is_array($arguments)) {
            $arguments = explode(' ', $arguments);
        }
        $options = array();
        $operands = array();
        $numArgs = count($arguments);
        for ($i = 0; $i < $numArgs; ++$i) {
            $arg = trim($arguments[$i]);
            if (empty($arg)) {
                continue;
            }
            if (($arg === '--') || ($arg === '-') || !String::startsWith($arg, '-')){
                // no more options, treat the remaining arguments as operands
                $firstOperandIndex = ($arg == '--') ? $i + 1 : $i;
                $operands = array_slice($arguments, $firstOperandIndex);
                break;
            }
            if (String::startsWith($arg, '--')) {
                $options = $this->addLongOption($options, $arguments, $i);
            } else {
                $options = $this->addShortOption($options, $arguments, $i);
            }
        }

        $options = $this->addDefaultValues($options);
        $operands = array_values(array_diff($operands, array('--')));
        return new Result($options, $operands);
    }

    private function addShortOption(array $options, $arguments, &$i)
    {
        $nextArg = $this->nextElement($arguments, $i);
        $option = String::substr($arguments[$i], 1);
        if (String::length($option) > 1) {
            // multiple options strung together
            $flags = String::split($option);
            foreach ($flags as $j => $flag) {
                if ($j === count($flags) - 1 && $this->canBeArgument($nextArg) && $this->optionHasArgument($flag)) {
                    // e.g. `ls -sw 100`
                    $options = $this->addOption($options, $flag, $nextArg);
                    ++$i;
                } else {
                    $options = $this->addOption($options, $flag, null);
                }
            }
        } else {
            if ($this->canBeArgument($nextArg) && $this->optionHasArgument($option)) {
                $options = $this->addOption($options, $option, $nextArg);
                ++$i;
            } else {
                $options = $this->addOption($options, $option, null);
            }
        }
        return $options;
    }

    private function addLongOption(array $options, $arguments, &$i)
    {
        $option = String::substr($arguments[$i], 2);
        if (String::contains($option, '=')) {
            list($option, $value) = explode('=', $option, 2);
        } else {
            $nextArg = $this->nextElement($arguments, $i);
            if ($this->canBeArgument($nextArg) && $this->optionHasArgument($option)) {
                $value = $nextArg;
                ++$i;
            } else {
                $value = null;
            }
        }
        return $this->addOption($options, $option, $value);
    }

    /**
     * Add an option to the list of known options.
     *
     * @param Option[] $options
     * @param string $string the option's name
     * @param string $value the option's value (or null)
     * @throws \UnexpectedValueException
     * @return Option[]
     */
    private function addOption(array $options, $string, $value)
    {
        foreach ($this->optionList as $option) {
            if (!$option->matches($string)) {
                continue;
            }
            if ($option->mode() == Getopt::REQUIRED_ARGUMENT && String::length($value) === 0) {
                throw new \UnexpectedValueException("Option '$string' must have a value");
            }
            if ($option->getArgument()->hasValidation()) {
                if ((String::length($value) > 0) && !$option->getArgument()->validates($value)) {
                    throw new \UnexpectedValueException("Option '$string' has an invalid value");
                }
            }
            // for no-argument options, check if they are duplicate (eg. '-vvv')
            if ($option->mode() == Getopt::NO_ARGUMENT) {
                $oldValue = isset($options[$string]) ? $options[$string] : null;
                $value = is_null($oldValue) ? 1 : $oldValue + 1;
            }
            // for optional-argument options, set value to 1 if none was given
            $value = (String::length($value) > 0) ? $value : 1;
            // add both long and short names (if they exist) to the option array to facilitate lookup
            if ($option->short()) {
                $options[$option->short()] = $value;
            }
            if ($option->long()) {
                $options[$option->long()] = $value;
            }
            return $options;
        }
        throw new \UnexpectedValueException("Option '$string' is unknown");
    }

    /**
     * If there are options with default values that were not overridden by the parsed option string,
     * add them to the list of known options.
     *
     * @param Option[] $options
     * @return Option[]
     */
    private function addDefaultValues(array $options)
    {
        foreach ($this->optionList as $option) {
            if ($option->getArgument()->hasDefaultValue()
                    && !isset($options[$option->short()])
                    && !isset($options[$option->long()])
            ) {
                if ($option->short()) {
                    $options = $this->addOption($options, $option->short(), $option->getArgument()->getDefaultValue());
                }
                if ($option->long()) {
                    $options = $this->addOption($options, $option->long(), $option->getArgument()->getDefaultValue());
                }
            }
        }
        return $options;
    }

    /**
     * Return true if the given option can take an argument, false if it can't or is unknown.
     *
     * @param string $name the option's name
     * @return boolean
     */
    private function optionHasArgument($name)
    {
        foreach ($this->optionList as $option) {
            if ($option->matches($name)) {
                return $option->mode() != Getopt::NO_ARGUMENT;
            }
        }
        return false;
    }

    private function nextElement(array $array, $index)
    {
        return ($index < count($array) - 1) ? $array[$index + 1] : null;
    }

    private function canBeArgument($string)
    {
        return !is_null($string) && (($string === '-') || !String::startsWith($string, '-'));
    }
}
