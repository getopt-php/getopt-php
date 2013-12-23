<?php

namespace Ulrichsg\Getopt;

class Option
{
    private $short;
    private $long;
    private $mode;
    private $description = '';
    private $hasDefault = false;
    private $default;

    public function __construct($short, $long, $mode = Getopt::NO_ARGUMENT)
    {
        if (!$short && !$long) {
            throw new \InvalidArgumentException("The short and long name of an option must not both be empty");
        }
        $this->setShort($short);
        $this->setLong($long);
        $this->setMode($mode);
    }

    /**
     * Returns true if the given string is equal to either the short or the long name.
     *
     * @param string $string
     * @return bool
     */
    public function matches($string)
    {
        return ($string == $this->short) || ($string == $this->long);
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

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function hasDefaultValue()
    {
        return $this->hasDefault;
    }

    public function getDefaultValue()
    {
        return $this->default;
    }

    public function setDefaultValue($value)
    {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
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
        if (!in_array($mode, array(Getopt::NO_ARGUMENT, Getopt::OPTIONAL_ARGUMENT, Getopt::REQUIRED_ARGUMENT), true)) {
            throw new \InvalidArgumentException("Option mode must be one of "
                ."Getopt::NO_ARGUMENT, Getopt::OPTIONAL_ARGUMENT and Getopt::REQUIRED_ARGUMENT");
        }
        $this->mode = $mode;
    }
}
