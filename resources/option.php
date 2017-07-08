<?php

use GetOpt\Getopt;

/** @var \GetOpt\Option $option */
/** @var int $padding */

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

$definition = str_pad(sprintf(
    '  %s %s',
    implode(', ', array_filter( array(
        $option->short() ? '-' . $option->short() : null,
        $option->long() ? '--' . $option->long() : null,
    ))),
    $argument
), $padding);

return sprintf("%s %s", $definition, $option->getDescription());
