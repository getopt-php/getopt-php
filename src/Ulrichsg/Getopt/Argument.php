<?php

namespace Ulrichsg\Getopt;

class Argument
{
    /** @var string */
    private $default;

    /**
     * Creates a new argument.
     * 
     * @param scalar|null $default Default value or NULL
     */
    public function __construct($default = null)
    {
        if (!is_null($default)) {
            $this->setDefaultValue($default);
        }
    }

    /**
     * Set the default value
     * 
     * @param scalar $value
     * @return Argument this object (for chaining calls)
     */
    public function setDefaultValue($value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException("Default value must be scalar");
        }
        $this->default = (string) $value;
        return $this;
    }

    /**
     * Check whether the argument has a defaul value
     * 
     * @return boolean
     */
    public function hasDefaultValue()
    {
        return !empty($this->default);
    }

    /**
     * Retrieve the default value
     * 
     * @return string|null
     */
    public function getDefaultValue()
    {
        return $this->default;
    }
}