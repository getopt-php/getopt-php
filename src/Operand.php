<?php

namespace GetOpt;

/**
 * Class Operand
 *
 * @package GetOpt
 * @author  Thomas Flori <thflori@gmail.com>
 */
class Operand extends Argument
{
    protected $required;

    /**
     * Operand constructor.
     *
     * @param string   $name       A name for the operand
     * @param bool     $required   Whether the operand is required
     * @param mixed    $default    Default value if not required
     * @param callable $validation A validation function
     */
    public function __construct($name, $required = false, $default = null, $validation = null)
    {
        $this->required = $required;
        parent::__construct($default, $validation, $name);
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return self
     */
    public function required($required = true)
    {
        $this->required = $required;
        return $this;
    }
}
