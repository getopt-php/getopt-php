<?php

namespace GetOpt;

/**
 * Class Argument
 *
 * @package GetOpt
 * @author  Ulrich Schmidt-Goertz
 */
class Argument
{
    use WithMagicGetter;

    const CLASSNAME = __CLASS__;

    /** @var mixed */
    protected $default;
    /** @var callable */
    protected $validation;
    /** @var string */
    protected $name;

    /**
     * Creates a new argument.
     *
     * @param mixed    $default    Default value or NULL
     * @param callable $validation A validation function
     * @param string   $name       A name for the argument
     */
    public function __construct($default = null, callable $validation = null, $name = "arg")
    {
        if (!is_null($default)) {
            $this->setDefaultValue($default);
        }
        if (!is_null($validation)) {
            $this->setValidation($validation);
        }
        $this->name = $name;
    }

    /**
     * Set the default value
     *
     * @param mixed $value The value has to be a scalar value
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setDefaultValue($value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException("Default value must be scalar");
        }
        $this->default = $value;
        return $this;
    }

    /**
     * Set a validation function.
     * The function must take a string and return true if it is valid, false otherwise.
     *
     * @param callable $callable
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setValidation(callable $callable)
    {
        $this->validation = $callable;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Check if an argument validates according to the specification.
     *
     * @param string $arg
     * @return bool
     */
    public function validates($arg)
    {
        return (bool)call_user_func($this->validation, $arg);
    }

    /**
     * Check if the argument has a validation function
     *
     * @return bool
     */
    public function hasValidation()
    {
        return isset($this->validation);
    }

    /**
     * Check whether the argument has a default value
     *
     * @return boolean
     */
    public function hasDefaultValue()
    {
        return !is_null($this->default);
    }

    /**
     * Retrieve the default value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->default;
    }

    /**
     * Retrieve the argument name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
