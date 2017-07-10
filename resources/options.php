Options:
<?php

use GetOpt\Getopt;

/** @var \GetOpt\Option[] $options */

$data            = array();
$definitionWidth = 0;
foreach ($options as $option) {
    $argument = '';
    if ($option->mode() !== Getopt::NO_ARGUMENT) {
        $argument = '<' . $option->getArgument()->getName() . '>';
        if ($option->mode() === Getopt::OPTIONAL_ARGUMENT) {
            $argument = '[' . $argument . ']';
        }
    }

    $definition = sprintf(
        '%s %s',
        implode(', ', array_filter( array(
            $option->short() ? '-' . $option->short() : null,
            $option->long() ? '--' . $option->long() : null,
        ))),
        $argument
    );

    if (strlen($definition) > $definitionWidth) {
        $definitionWidth = strlen($definition);
    }

    $data[] = array(
        $definition,
        $option->getDescription()
    );
}

$screenWidth = @getenv('COLUMNS') ?: @exec('tput cols 2>/dev/null');
$screenWidth = $screenWidth ?: 90;
$screenWidth = min(array(120, $screenWidth));
foreach ($data as $dataRow) {
    $row = sprintf('  % -' . $definitionWidth . 's  %s', $dataRow[0], $dataRow[1]);

    while (mb_strlen($row) > $screenWidth) {
        $p = strrpos(substr($row, 0, $screenWidth), ' ');
        echo substr($row, 0, $p) . PHP_EOL;
        $row = sprintf('  %s  %s', str_repeat(' ', $definitionWidth), substr($row, $p+1));
    }

    echo $row . PHP_EOL;
}
