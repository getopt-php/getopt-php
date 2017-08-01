<?php

namespace GetOpt;

use GetOpt\ArgumentException\Invalid;

/**
 * Class Operand
 *
 * @package GetOpt
 * @author  Thomas Flori <thflori@gmail.com>
 */
class Operand extends Argument
{
    const OPTIONAL = 0;
    const REQUIRED = 1;
    const MULTIPLE = 2;

    /** @var int */
    protected $mode;

    /** @var mixed */
    protected $value;

    /**
     * Operand constructor.
     *
     * @param string $name A name for the operand
     * @param int    $mode The operand mode
     */
    public function __construct($name, $mode = self::OPTIONAL)
    {
        $this->mode = $mode;
        parent::__construct(null, null, $name);
    }

    /**
     * Fluent interface for constructor
     *
     * @param string $name
     * @param int    $mode
     * @return static
     */
    public static function create($name, $mode = 0)
    {
        return new static($name, $mode);
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return (bool)($this->mode & self::REQUIRED);
    }

    public function isMultiple()
    {
        return (bool)($this->mode & self::MULTIPLE);
    }

    /**
     * @param bool $required
     * @return self
     */
    public function required($required = true)
    {
        $this->mode += $required ? Operand::REQUIRED : Operand::REQUIRED * -1;
        return $this;
    }

    /**
     *  Internal method to set the current value
     *
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        if ($this->validation && !$this->validates($value)) {
            throw new Invalid(sprintf('Operand %s has an invalid value', $this->name));
        }

        if ($this->isMultiple()) {
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
    public function value()
    {
        if ($this->value !== null) {
            return $this->value;
        }

        if ($this->isMultiple()) {
            return $this->default !== null ? [ $this->default ] : null;
        }

        return $this->default;
    }

    /**
     * Get a string from value
     *
     * @return string
     */
    public function __toString()
    {
        $value = $this->value();
        return !is_array($value) ? (string)$value : implode(',', $value);
    }
}
