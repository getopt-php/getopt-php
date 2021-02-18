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
class Option implements Describable
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
     * @param ?string  $short The option's short name (one of [a-zA-Z0-9?!§$%#]) or null for long-only options
     * @param ?string  $long  The option's long name (a string of 1+ letter|digit|_|- characters, starting with a letter
     *                        or digit) or null for short-only options
     * @param string   $mode  Whether the option can/must have an argument (optional, defaults to no argument)
     */
    public function __construct(?string $short, string $long = null, string $mode = GetOpt::NO_ARGUMENT)
    {
        if (!$short && !$long) {
            throw new \InvalidArgumentException("The short and long name may not both be empty");
        }
        if ($short == $long) {
            throw new \InvalidArgumentException("The short and long names have to be unique");
        }
        $this->setShort($short);
        $this->setLong($long);
        $this->setMode($mode);
        $this->argument = new Argument();
        $this->argument->multiple($this->mode === GetOpt::MULTIPLE_ARGUMENT);
        $this->argument->setOption($this);
    }

    /**
     * Fluent interface for constructor so options can be added during construction
     *
     * @see Options::__construct()
     * @param ?string  $short
     * @param ?string  $long
     * @param string   $mode
     * @return static
     */
    public static function create(?string $short, string $long = null, string $mode = GetOpt::NO_ARGUMENT): Option
    {
        return new static($short, $long, $mode);
    }

    /**
     * Defines a description for the option. This is only used for generating usage information.
     *
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): Option
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Defines a default value for the option.
     *
     * @param mixed $value
     * @return Option this object (for chaining calls)
     */
    public function setDefaultValue($value): Option
    {
        $this->argument->setDefaultValue($value);
        return $this;
    }

    /**
     * Defines a validation function for the option.
     *
     * @param callable        $function
     * @param string|callable $message
     * @return Option this object (for chaining calls)
     */
    public function setValidation(callable $function, $message = null): Option
    {
        $this->argument->setValidation($function, $message);
        return $this;
    }

    /**
     * Set the argumentName.
     *
     * @param string $name
     * @return $this
     */
    public function setArgumentName(string $name): Option
    {
        $this->argument->setName($name);
        return $this;
    }

    /**
     * Sets the argument object directly.
     *
     * @param Argument $arg
     * @return $this
     */
    public function setArgument(Argument $arg): Option
    {
        if ($this->mode == GetOpt::NO_ARGUMENT) {
            throw new \InvalidArgumentException("Option should not have any argument");
        }
        $this->argument = clone $arg; // he can reuse his arg but we need a unique arg
        $this->argument->multiple($this->mode === GetOpt::MULTIPLE_ARGUMENT);
        $this->argument->setOption($this);
        return $this;
    }

    /**
     * Change the short name
     *
     * @param ?string $short
     * @return $this
     */
    public function setShort(?string $short): Option
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

    public function getShort(): ?string
    {
        return $this->short;
    }

    /**
     * Returns long name or short name if long name is not set
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getLong() ?: $this->getShort();
    }

    /**
     * Change the long name
     *
     * @param ?string $long
     * @return $this
     */
    public function setLong(?string $long): Option
    {
        if (!(is_null($long) || preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/", $long))) {
            throw new \InvalidArgumentException(sprintf(
                'Long option must be null or an alphanumeric string, found \'%s\'',
                $long
            ));
        }
        $this->long = $long;
        return $this;
    }

    public function getLong(): ?string
    {
        return $this->long;
    }

    /**
     * Change the mode
     *
     * @param string $mode
     * @return $this
     */
    public function setMode(string $mode): Option
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

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Retrieve the argument object
     *
     * @return Argument
     */
    public function getArgument(): Argument
    {
        return $this->argument;
    }

    /**
     * Internal method to set the current value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value = null): Option
    {
        if ($value === null) {
            if (in_array($this->mode, [ GetOpt::REQUIRED_ARGUMENT, GetOpt::MULTIPLE_ARGUMENT ])) {
                throw new Missing(sprintf(
                    GetOpt::translate('option-argument-missing'),
                    $this->getName()
                ));
            }

            $value = $this->argument->getValue() +1;
        }

        $this->argument->setValue($value);

        return $this;
    }

    /**
     * Get the current value
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->argument->getValue();
        return $value === null || $value === [] ? $this->argument->getDefaultValue() : $value;
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

    /**
     * Returns a human readable string representation of the object
     *
     * @return string
     */
    public function describe()
    {
        return sprintf('%s \'%s\'', GetOpt::translate('option'), $this->getName());
    }
}
