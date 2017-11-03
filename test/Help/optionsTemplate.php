<?php

// An example of a options template

use GetOpt\GetOpt;

echo PHP_EOL . 'Available options:' . PHP_EOL;

/** @var \GetOpt\Option[] $options */

$data            = [];
$definitionWidth = 0;
foreach ($options as $option) {
    $definition = implode(', ', array_filter([
        $option->short() ? '-' . $option->short() : null,
        $option->long() ? '--' . $option->long() : null,
    ]));

    if ($option->mode() !== GetOpt::NO_ARGUMENT) {
        $name = $option->getArgument()->getName();
        $argument = '<' . $name . '>';
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

echo $this->renderColumns($definitionWidth, $data);
