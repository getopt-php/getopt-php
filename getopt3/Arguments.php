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

    public static function fromString($argsString)
    {
        $argv = array();
        $argsString = trim($argsString);
        $argc = 0;

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
