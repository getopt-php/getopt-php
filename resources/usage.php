<?php

echo 'Usage: ' . $getopt->get(GetOpt\Getopt::SETTING_SCRIPT_NAME) . ' ';

$command = $getopt->getCommand();
if ($command) {
    echo $command->getName() . ' ';
} elseif ($getopt->hasCommands()) {
    echo '[command] ';
}

echo '[options] [operands]' . PHP_EOL;

if ($command) {
    echo PHP_EOL . $command->getDescription() . PHP_EOL . PHP_EOL;
}
