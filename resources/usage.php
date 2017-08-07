<?php

use GetOpt\Command;
use GetOpt\GetOpt;

/** @var GetOpt $getopt */
/** @var Command $command */

echo 'Usage: ' . $getopt->get(GetOpt::SETTING_SCRIPT_NAME) . ' ';

if (isset($command)) {
    echo $command->name() . ' ';
} elseif ($getopt->hasCommands()) {
    echo '<command> ';
}

if ($getopt->hasOptions() || !$getopt->get(GetOpt::SETTING_STRICT_OPTIONS)) {
    echo '[options] ';
}

$lastOperandMultiple = false;
if ($getopt->hasOperands()) {
    foreach ($getopt->getOperandObjects() as $operand) {
        $name = '<' . $operand->getName() . '>';
        if (!$operand->isRequired()) {
            $name = '[' . $name . ']';
        }
        echo $name . ' ';
        if ($operand->isMultiple()) {
            echo '[<' . $operand->getName() . '>...]';
            $lastOperandMultiple = true;
        }
    }
}

if (!$lastOperandMultiple && !$getopt->get(GetOpt::SETTING_STRICT_OPERANDS)) {
    echo '[operands]';
}

echo PHP_EOL;

if (isset($command)) {
    echo PHP_EOL . $command->description() . PHP_EOL . PHP_EOL;
}
