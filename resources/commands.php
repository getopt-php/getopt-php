<?php

echo PHP_EOL . 'Commands:' . PHP_EOL;

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

$screenWidth = defined('COLUMNS') ? COLUMNS : @getenv('COLUMNS') ?: @exec('tput cols 2>/dev/null') ?: 90;
$screenWidth = min([ isset($maxWidth) ? $maxWidth : 120, $screenWidth ]);
foreach ($data as $dataRow) {
    $row = sprintf('  % -' . $nameWidth . 's  %s', $dataRow[0], $dataRow[1]);

    while (mb_strlen($row) > $screenWidth) {
        $p = strrpos(substr($row, 0, $screenWidth), ' ');
        echo substr($row, 0, $p) . PHP_EOL;
        $row = sprintf('  %s  %s', str_repeat(' ', $nameWidth), substr($row, $p + 1));
    }

    echo $row . PHP_EOL;
}
