<?php

use GetOpt\Getopt;

/** @var string $banner */
/** @var string $scriptName */

// backward compatibility
if ($banner) {
    return array(trim(sprintf($banner, $scriptName)));
}

return array(sprintf("Usage: %s [options] [operands]", $scriptName));
