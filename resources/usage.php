<?php

/** @var \GetOpt\GetOpt $getopt */
/** @var \GetOpt\Command $command */

echo 'Usage: ' . $getopt->get(GetOpt\GetOpt::SETTING_SCRIPT_NAME) . ' ';

if (isset($command)) {
    echo $command->getName() . ' ';
} elseif ($getopt->hasCommands()) {
    echo '[command] ';
}

if ($getopt->hasOptions()) {
    echo '[options] ';
}

$lastOperandMultiple = false;
if ($getopt->hasOperands()) {
    foreach ($getopt->getOperands(true) as $operand) {
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

if (!$lastOperandMultiple) {
    echo '[operands]';
}

echo PHP_EOL;

if (isset($command)) {
    echo PHP_EOL . $command->getDescription() . PHP_EOL . PHP_EOL;
}
