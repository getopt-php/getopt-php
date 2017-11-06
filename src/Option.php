<?php

namespace GetOpt;

use GetOpt\ArgumentException\Invalid;
use GetOpt\ArgumentException\Missing;

/**
 * Represents an option that GetOpt accepts.
 *
 * @package GetOpt
 * @author  Ulrich Schmidt-Goertz
 */
class Option
{
    use WithMagicGetter;

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
     * @param string   $short The option's short name (one of [a-zA-Z0-9?!§$%#]) or null for long-only options
     * @param string   $long  The option's long name (a string of 2+ letter/digit/_/- characters, starting with a letter
     *                        or digit) or null for short-only options
     * @param string   $mode  Whether the option can/must have an argument (optional, defaults to no argument)
     */
    public function __construct($short, $long = null, $mode = GetOpt::NO_ARGUMENT)
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
     * Fluent interface for constructor so options can be added during construction
     *
     * @see Options::__construct()
     * @param string   $short
     * @param string   $long
     * @param string   $mode
     * @return static
     */
    public static function create($short, $long = null, $mode = GetOpt::NO_ARGUMENT)
    {
        return new static($short, $long, $mode);
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @deprecated will be removed in version 4
     * @see getDescription
     * @codeCoverageIgnore
     */
    public function description()
    {
        return $this->description;
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
     * Set the argumentName.
     *
     * @param $name
     * @return $this
     */
    public function setArgumentName($name)
    {
        $this->argument->setName($name);
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
        if ($this->mode == GetOpt::NO_ARGUMENT) {
            throw new \InvalidArgumentException("Option should not have any argument");
        }
        $this->argument = $arg;
        return $this;
    }

    /**
     * Change the short name
     *
     * @param string $short
     * @return Option this object (for chaining calls)
     */
    public function setShort($short)
    {
        if (!(is_null($short) || preg_match("/^[a-zA-Z0-9?!§$%#]$/", $short))) {
            throw new \InvalidArgumentException(sprintf(
                'Short option must be null or one of [a-zA-Z0-9?!§$%%#], found \'%s\'',
                $short
            ));
        }
        $this->short = $short;
        return $this;
    }

    /**
     * @return string
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * @deprecated will be removed in version 4
     * @see getShort
     * @codeCoverageIgnore
     */
    public function short()
    {
        return $this->short;
    }

    /**
     * Change the long name
     *
     * @param $long
     * @return Option this object (for chaining calls)
     */
    public function setLong($long)
    {
        if (!(is_null($long) || preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_-]{1,}$/", $long))) {
            throw new \InvalidArgumentException(sprintf(
                'Long option must be null or an alphanumeric string, found \'%s\'',
                $long
            ));
        }
        $this->long = $long;
        return $this;
    }

    /**
     * @return string
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @deprecated will be removed in version 4
     * @see getLong
     * @codeCoverageIgnore
     */
    public function long()
    {
        return $this->long;
    }

    /**
     * Change the mode
     *
     * @param $mode
     * @return Option this object (for chaining calls)
     */
    public function setMode($mode)
    {
        if (!in_array($mode, [
            GetOpt::NO_ARGUMENT,
            GetOpt::OPTIONAL_ARGUMENT,
            GetOpt::REQUIRED_ARGUMENT,
            GetOpt::MULTIPLE_ARGUMENT,
        ], true)) {
            throw new \InvalidArgumentException(sprintf(
                'Option mode must be one of %s, %s, %s and %s',
                'GetOpt::NO_ARGUMENT',
                'GetOpt::OPTIONAL_ARGUMENT',
                'GetOpt::REQUIRED_ARGUMENT',
                'GetOpt::MULTIPLE_ARGUMENT'
            ));
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @deprecated will be removed in version 4
     * @see getMode
     * @codeCoverageIgnore
     */
    public function mode()
    {
        return $this->mode;
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

    /**
     * Internal method to set the current value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value = null)
    {
        if ($value === null && in_array($this->mode, [ GetOpt::REQUIRED_ARGUMENT, GetOpt::MULTIPLE_ARGUMENT ])) {
            throw new Missing(sprintf(
                'Option \'%s\' must have a value',
                $this->getLong() ?: $this->getShort()
            ));
        }

        if ($value === null || $this->getMode() === GetOpt::NO_ARGUMENT) {
            $value = $this->value === null ? 1 : $this->value + 1;
        }

        if ($this->getArgument()->hasValidation() && !$this->getArgument()->validates($value)) {
            throw new Invalid(sprintf(
                'Option \'%s\' has an invalid value',
                $this->getLong() ?: $this->getShort()
            ));
        }

        if ($this->mode === GetOpt::MULTIPLE_ARGUMENT) {
            $this->value = $this->value === null ? [ $value ] : array_merge($this->value, [ $value ]);
        } else {
            $this->value = $value;
        }

        return $this;
    }

    /**
     * Get the current value
     *
     * @return mixed
     */
    public function getValue()
    {
        switch ($this->mode) {
            case GetOpt::OPTIONAL_ARGUMENT:
            case GetOpt::REQUIRED_ARGUMENT:
                return $this->value === null ? $this->argument->getDefaultValue() : $this->value;

            case GetOpt::MULTIPLE_ARGUMENT:
                if ($this->value === null) {
                    return $this->argument->getDefaultValue() ? [ $this->argument->getDefaultValue() ] : [];
                }
                return $this->value;

            case GetOpt::NO_ARGUMENT:
            default:
                return $this->value;
        }
    }

    /**
     * @deprecated will be removed in version 4
     * @see getValue
     * @codeCoverageIgnore
     */
    public function value()
    {
        return $this->getValue();
    }

    /**
     * Get a string from value
     *
     * @return string
     */
    public function __toString()
    {
        $value = $this->getValue();
        return !is_array($value) ? (string)$value : implode(',', $value);
    }
}
