<?php

use GetOpt\Getopt;

/** @var string $banner */
/** @var string $scriptName */

if ($banner) {
    return sprintf($banner, $scriptName);
}

return sprintf("Usage: %s [options] [operands]", $scriptName);
