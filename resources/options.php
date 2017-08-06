<?php

use GetOpt\GetOpt;

echo PHP_EOL . 'Options:' . PHP_EOL;

/** @var \GetOpt\Option[] $options */

$data            = [];
$definitionWidth = 0;
foreach ($options as $option) {
    $definition = implode(', ', array_filter([
        $option->short() ? '-' . $option->short() : null,
        $option->long() ? '--' . $option->long() : null,
    ]));

    if ($option->mode() !== GetOpt::NO_ARGUMENT) {
        $argument = '<' . $option->getArgument()->getName() . '>';
        if ($option->mode() === GetOpt::OPTIONAL_ARGUMENT) {
            $argument = '[' . $argument . ']';
        }

        $definition .= ' ' . $argument;
    }

    if (strlen($definition) > $definitionWidth) {
        $definitionWidth = strlen($definition);
    }

    $data[] = [
        $definition,
        $option->description()
    ];
}

$screenWidth = defined('COLUMNS') ? COLUMNS : @getenv('COLUMNS') ?: @exec('tput cols 2>/dev/null') ?: 90;
$screenWidth = min([ isset($maxWidth) ? $maxWidth : 120, $screenWidth ]);
foreach ($data as $dataRow) {
    $row = sprintf('  % -' . $definitionWidth . 's  %s', $dataRow[0], $dataRow[1]);

    while (mb_strlen($row) > $screenWidth) {
        $p = strrpos(substr($row, 0, $screenWidth), ' ');
        echo substr($row, 0, $p) . PHP_EOL;
        $row = sprintf('  %s  %s', str_repeat(' ', $definitionWidth), substr($row, $p+1));
    }

    echo $row . PHP_EOL;
}
