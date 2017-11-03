<?php

// An example of a commands template

echo PHP_EOL . 'Available commands:' . PHP_EOL;

/** @var \GetOpt\Command[] $commands */

$data      = [];
$nameWidth = 0;
foreach ($commands as $command) {
    if (strlen($command->name()) > $nameWidth) {
        $nameWidth = strlen($command->name());
    }

    $data[] = [
        $command->name(),
        $command->description(true)
    ];
}

echo $this->renderColumns($nameWidth, $data);
