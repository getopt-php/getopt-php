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
    const TRANSLATION_KEY = 'operand';

    const OPTIONAL = 0;
    const REQUIRED = 1;
    const MULTIPLE = 2;

    /** @var bool */
    protected $required;

    /** @var string */
    protected $description = '';

    /**
     * Operand constructor.
     *
     * @param string $name A name for the operand
     * @param int    $mode The operand mode
     */
    public function __construct(string $name, int $mode = self::OPTIONAL)
    {
        $this->required = (bool)($mode & self::REQUIRED);
        $this->multiple = (bool)($mode & self::MULTIPLE);

        parent::__construct(null, null, $name);
    }

    /**
     * Fluent interface for constructor
     *
     * @param string $name
     * @param int    $mode
     * @return static
     */
    public static function create(string $name, int $mode = 0): Operand
    {
        return new static($name, $mode);
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return $this
     */
    public function required(bool $required = true): Operand
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Get the current value
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = parent::getValue();
        return $value === null || $value === [] ? $this->getDefaultValue() : $value;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): Operand
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get a string from value
     *
     * @return string
     */
    public function __toString(): string
    {
        $value = $this->getValue();
        return !is_array($value) ? (string)$value : implode(',', $value);
    }
}
