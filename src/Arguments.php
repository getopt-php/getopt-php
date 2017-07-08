<?php

namespace GetOpt;

class Arguments
{
    /** @var string[] */
    protected $arguments;

    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    public function process(Getopt $getopt, callable $addOperand)
    {
        while (($arg = array_shift($this->arguments)) !== null) {
            if ($this->isMeta($arg)) {
                // everything from here are operands
                foreach ($this->arguments as $argument) {
                    $addOperand($argument);
                }
                break;
            }

            if ($this->isValue($arg)) {
                $addOperand($arg);
            }

            if ($this->isLongOption($arg)) {
                $name   = $this->longName($arg);
                $option = $getopt->getOption($name, true);

                if (!$option) {
                    throw new \UnexpectedValueException(sprintf(
                        'Option \'%s\' is unknown',
                        $name
                    ));
                }

                $value = null;
                if ($option->mode() !== Getopt::NO_ARGUMENT) {
                    $value = $this->value($arg);
                }

                $option->setValue($value);
                continue;
            }

            // the only left is short options
            foreach ($this->shortNames($arg) as $name) {
                $option = $getopt->getOption($name, true);

                if (!$option) {
                    throw new \UnexpectedValueException(sprintf(
                        'Option \'%s\' is unknown',
                        $name
                    ));
                }

                $value = null;
                if ($option->mode() !== Getopt::NO_ARGUMENT) {
                    $value = $this->value($arg, $name);
                }

                $option->setValue($value);
                if ($value) {
                    // when there is a value it was the last option
                    break;
                }
            }
        }
        return true;
    }

    protected function isOption($arg)
    {
        return !$this->isValue($arg) && !$this->isMeta($arg);
    }

    protected function isValue($arg)
    {
        return (empty($arg) || $arg === '-' || $arg[0] !== '-');
    }

    protected function isMeta($arg)
    {
        return $arg && $arg === '--';
    }

    protected function isLongOption($arg)
    {
        return $this->isOption($arg) && $arg[1] === '-';
    }

    protected function longName($arg)
    {
        $name = substr($arg, 2);
        $p    = strpos($name, '=');
        return $p ? substr($name, 0, $p) : $name;
    }

    protected function shortNames($arg)
    {
        if (!$this->isOption($arg) || $this->isLongOption($arg)) {
            return array();
        }

        return array_map(function ($i) use ($arg) {
            return mb_substr($arg, $i, 1);
        }, range(1, mb_strlen($arg) -1));
    }

    protected function value($arg, $name = null)
    {
        $p = strpos($arg, $this->isLongOption($arg) ? '=' : $name);
        if ($this->isLongOption($arg) && $p || !$this->isLongOption($arg) && $p < strlen($arg)-1) {
            return substr($arg, $p+1);
        }

        if (!empty($this->arguments) && $this->isValue($this->arguments[0])) {
            return array_shift($this->arguments);
        }

        return null;
    }

    /**
     * Parse arguments from argument string
     *
     * @param string $argsString
     * @return Arguments
     */
    public static function fromString($argsString)
    {
        $argv = array('');
        $argsString = trim($argsString);
        $argc = 0;

        if (empty($argsString)) {
            return new self([]);
        }

        $state = 'n'; // states: n (normal), d (double quoted), s(single quoted)
        for ($i = 0; $i < strlen($argsString); $i++) {
            $char = $argsString{$i};
            switch ($state) {
                case 'n':
                    if ($char === '\'') {
                        $state = 's';
                    } elseif ($char === '"') {
                        $state = 'd';
                    } elseif (in_array($char, array("\n", "\t", ' '))) {
                        $argc++;
                        $argv[$argc] = '';
                    } else {
                        $argv[$argc] .= $char;
                    }
                    break;

                case 's':
                    if ($char === '\'') {
                        $state = 'n';
                    } else {
                        $argv[$argc] .= $char;
                    }
                    break;

                case 'd':
                    if ($char === '"') {
                        $state = 'n';
                    } else {
                        $argv[$argc] .= $char;
                    }
                    break;
            }
        }

        return new self($argv);
    }
}
