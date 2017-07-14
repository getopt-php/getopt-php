<?php

echo 'Commands:' . PHP_EOL;

/** @var \GetOpt\Command[] $commands */

$data      = array();
$nameWidth = 0;
foreach ($commands as $command) {
    if (strlen($command->getName()) > $nameWidth) {
        $nameWidth = strlen($command->getName());
    }

    $data[] = array(
        $command->getName(),
        $command->getDescription(true)
    );
}

$screenWidth = defined('COLUMNS') ? COLUMNS : @getenv('COLUMNS') ?: @exec('tput cols 2>/dev/null') ?: 90;
$screenWidth = min(array(120, $screenWidth)); // max 120
foreach ($data as $dataRow) {
    $row = sprintf('  % -' . $nameWidth . 's  %s', $dataRow[0], $dataRow[1]);

    while (mb_strlen($row) > $screenWidth) {
        $p = strrpos(substr($row, 0, $screenWidth), ' ');
        echo substr($row, 0, $p) . PHP_EOL;
        $row = sprintf('  %s  %s', str_repeat(' ', $nameWidth), substr($row, $p + 1));
    }

    echo $row . PHP_EOL;
}
