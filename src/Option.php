<?php

namespace GetOpt;

/**
 * Represents an option that Getopt accepts.
 */
class Option
{
    const CLASSNAME = __CLASS__;

    private $short;
    private $long;
    private $mode;
    private $description = '';
    private $argument;
    private $value = null;

    /**
     * Creates a new option.
     *
     * @param string $short the option's short name (a single letter or digit) or null for long-only options
     * @param string $long  the option's long name (a string of 2+ letter/digit/_/- characters, starting with a letter
     *                      or digit) or null for short-only options
     * @param int    $mode  whether the option can/must have an argument (one of the constants defined in the Getopt
     *                      class)
     *                      (optional, defaults to no argument)
     * @throws \InvalidArgumentException if both short and long name are null
     */
    public function __construct($short, $long = null, $mode = Getopt::NO_ARGUMENT)
    {
        if (!$short && !$long) {
            throw new \InvalidArgumentException("The short and long name may not both be empty");
        }
        $this->setShort($short);
        $this->setLong($long);
        $this->setMode($mode);
        $this->argument = new Argument();
    }

    /**
     * Defines a description for the option. This is only used for generating usage information.
     *
     * @param string $description
     * @return Option this object (for chaining calls)
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Defines a default value for the option.
     *
     * @param mixed $value
     * @return Option this object (for chaining calls)
     */
    public function setDefaultValue($value)
    {
        $this->argument->setDefaultValue($value);
        return $this;
    }

    /**
     * Defines a validation function for the option.
     *
     * @param callable $function
     * @return Option this object (for chaining calls)
     */
    public function setValidation($function)
    {
        $this->argument->setValidation($function);
        return $this;
    }

    /**
     * Sets the argument object directly.
     *
     * @param Argument $arg
     * @return Option this object (for chaining calls)
     */
    public function setArgument(Argument $arg)
    {
        if ($this->mode == Getopt::NO_ARGUMENT) {
            throw new \InvalidArgumentException("Option should not have any argument");
        }
        $this->argument = $arg;
        return $this;
    }

    /**
     * Returns true if the given string is equal to either the short or the long name.
     *
     * @param string $string
     * @return bool
     */
    public function matches($string)
    {
        if ($string === null) {
            return false;
        }

        return ($string === $this->short) || ($string === $this->long);
    }

    public function short()
    {
        return $this->short;
    }

    public function long()
    {
        return $this->long;
    }

    public function mode()
    {
        return $this->mode;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Retrieve the argument object
     *
     * @return Argument
     */
    public function getArgument()
    {
        return $this->argument;
    }

    public function setValue($value = null)
    {
        if ($value === null && in_array($this->mode, array(Getopt::REQUIRED_ARGUMENT, Getopt::MULTIPLE_ARGUMENT))) {
            throw new \UnexpectedValueException(sprintf(
                'Option \'%s\' must have a value',
                $this->long() ?: $this->short()
            ));
        }

        if ($value !== null && $this->mode !== Getopt::NO_ARGUMENT) {
            if ($this->getArgument()->hasValidation() && !$this->getArgument()->validates($value)) {
                throw new \UnexpectedValueException(sprintf(
                    'Option \'%s\' has an invalid value',
                    $this->long() ?: $this->short()
                ));
            }

            if ($this->mode === Getopt::MULTIPLE_ARGUMENT) {
                $this->value = $this->value === null ? array($value) : array_merge($this->value, array($value));
            } else {
                $this->value = $value;
            }
        } elseif ($this->mode() !== Getopt::OPTIONAL_ARGUMENT || !is_string($this->value)) {
            $this->value = $this->value === null ? 1 : $this->value + 1;
        }
    }

    public function getValue()
    {
        switch ($this->mode) {
            case Getopt::OPTIONAL_ARGUMENT:
            case Getopt::REQUIRED_ARGUMENT:
                return $this->value === null ? $this->argument->getDefaultValue() : $this->value;

            case Getopt::MULTIPLE_ARGUMENT:
                return $this->value === null ? array($this->argument->getDefaultValue()) : $this->value;

            case Getopt::NO_ARGUMENT:
            default:
                return $this->value;
        }
    }

    public function __toString()
    {
        $value = $this->getValue();
        return !is_array($value) ? $value . '' : implode(',', $value);
    }

    /**
     * Fluent interface for constructor so options can be added during construction
     *
     * @see Options::__construct()
     */
    public static function create($short, $long, $mode = Getopt::NO_ARGUMENT)
    {
        return new self($short, $long, $mode);
    }

    private function setShort($short)
    {
        if (!(is_null($short) || preg_match("/^[a-zA-Z0-9]$/", $short))) {
            throw new \InvalidArgumentException("Short option must be null or a letter/digit, found '$short'");
        }
        $this->short = $short;
    }

    private function setLong($long)
    {
        if (!(is_null($long) || preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_-]{1,}$/", $long))) {
            throw new \InvalidArgumentException("Long option must be null or an alphanumeric string, found '$long'");
        }
        $this->long = $long;
    }

    private function setMode($mode)
    {
        if (!in_array($mode, array(
            Getopt::NO_ARGUMENT,
            Getopt::OPTIONAL_ARGUMENT,
            Getopt::REQUIRED_ARGUMENT,
            Getopt::MULTIPLE_ARGUMENT,
        ), true)) {
            throw new \InvalidArgumentException(sprintf(
                'Option mode must be one of %s, %s, %s and %s',
                'Getopt::NO_ARGUMENT',
                'Getopt::OPTIONAL_ARGUMENT',
                'Getopt::REQUIRED_ARGUMENT',
                'Getopt::MULTIPLE_ARGUMENT'
            ));
        }
        $this->mode = $mode;
    }
}
