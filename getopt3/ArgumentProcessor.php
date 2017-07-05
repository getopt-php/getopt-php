<?php

namespace GetOpt;

class ArgumentProcessor
{
    public static function process(Getopt $getopt, Arguments $arguments)
    {
        while ($arg = $arguments->next()) {
            if ($arg === '--') {
                $getopt->__addOperands($arguments->rest());
            } elseif (empty($arg) || $arg === '-' || $arg[0] !== '-') {
                $getopt->__addOperands([$arg]);
            } elseif ($arg[1] === '-') {
                $p = strpos($arg, '=');
                if ($p !== false) {
                    $name = substr($arg, 2, $p);
                    $arguments->unshift(substr($arg, $p + 1));
                } else {
                    $name = substr($arg, 2);
                }
                $option = $getopt->getOption($name);
                if (!$option) {
                    throw new \UnexpectedValueException(sprintf(
                        'Option \'%s\' is unknown',
                        $name
                    ));
                }
                $option->retrieveValue($arguments);
            }
        }
    }
}
