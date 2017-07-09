<?php

use GetOpt\Getopt;


/** @var \GetOpt\Option[] $options */
/** @var int $padding */

$table = new \LucidFrame\Console\ConsoleTable();
$table->setIndent(1)->hideBorder();
foreach ($options as $option) {
    switch ($option->mode()) {
        case Getopt::OPTIONAL_ARGUMENT:
            $argument = '[<' . $option->getArgument()->getName() . '>]';
            break;

        case GetOpt::REQUIRED_ARGUMENT:
        case GetOpt::MULTIPLE_ARGUMENT:
            $argument = '<' . $option->getArgument()->getName() . '>';
            break;

        case Getopt::NO_ARGUMENT:
        default:
            $argument = '';
    }


    $table->addRow(array(
        // col1:
        sprintf(
            '%s %s',
            implode(', ', array_filter( array(
                $option->short() ? '-' . $option->short() : null,
                $option->long() ? '--' . $option->long() : null,
            ))),
            $argument
        ),
        // col2:
        $option->getDescription()
    ));
}

return array_merge(array('Options:'), array_filter(explode(PHP_EOL, $table->getTable())));
