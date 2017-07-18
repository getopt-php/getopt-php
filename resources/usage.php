<?php

/** @var \GetOpt\Getopt $getopt */
/** @var \GetOpt\Command $command */

echo 'Usage: ' . $getopt->get(GetOpt\Getopt::SETTING_SCRIPT_NAME) . ' ';

if (isset($command)) {
    echo $command->getName() . ' ';
} elseif ($getopt->hasCommands()) {
    echo '[command] ';
}

if ($getopt->hasOptions()) {
    echo '[options] ';
}

echo '[operands]' . PHP_EOL;

if (isset($command)) {
    echo PHP_EOL . $command->getDescription() . PHP_EOL . PHP_EOL;
}
