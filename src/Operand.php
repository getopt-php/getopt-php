<?php

namespace GetOpt;

class Operand extends Argument
{
    protected $required;

    public function __construct($name, $required = false, $default = null, $validation = null)
    {
        $this->required = $required;
        parent::__construct($default, $validation, $name);
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function required($required = true)
    {
        $this->required = $required;
        return $this;
    }
}
