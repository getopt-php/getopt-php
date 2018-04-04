<?php

namespace GetOpt;

trait WithValidator
{
    /** @var Validator */
    protected $validator;

    /**
     * Set a validation function.
     * The function must take a string or a callable and return true if it is valid, false otherwise.
     *
     * @param callable $callable
     * @param string   $message
     */
    public function setValidation(callable $callable, $message = '')
    {
        $this->validator = new Validator($callable, $message);
        return $this;
    }

    /**
     * Check whether a validator has been defined
     *
     * @return bool
     */
    protected function hasValidator()
    {
        return null !== $this->validator;
    }

    /**
     * Check if an argument validates according to the specification.
     *
     * @param  mixed $arg The value has to be a scalar
     * @return bool
     */
    public function validates($arg)
    {
        return $this->hasValidator() ? $this->validator->validates($arg) : true;
    }

    /**
     * Returns the validation message from the validator or a default one
     *
     * @return string
     */
    public function getValidationMessage()
    {
        if ($this->hasValidator() && $message = $this->validator->getMessage()) {
            return sprintf($message, $this->getName());
        }

        $type = ($this instanceof Operand) ? 'Operand' : 'Option';

        return sprintf("%s '%s' has an invalid value", $type, $this->getName());
    }
}
