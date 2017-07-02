---
layout: default
title: Example Code
permalink: /basic/example-code.html
---
# {{ page.title }}

This short (and incomplete) sample program demonstrates the use of Getopt's basic features. It is modeled after
[GNU mkdir](http://unixhelp.ed.ac.uk/CGI/man-cgi?mkdir) as an example of a real-world command line
interface that uses many of the aspects featured in Getopt.

```php
<?php

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

$getopt = new Getopt(array(
    new Option('m', 'mode', Getopt::REQUIRED_ARGUMENT),
    new Option('p', 'parents'),
    new Option('v', 'verbose'),
    new Option('Z', 'context', Getopt::REQUIRED_ARGUMENT),
    new Option(null, 'help'),
    new Option(null, 'version')
));

try {
    $getopt->parse();

    if ($getopt['version']) {
        echo "Getopt example v0.0.1\n";
        exit(0);
    }

    // Error handling and --help functionality omitted for brevity

    $createParents = ($getopt['parents'] > 0);
    // Note that these are null if the respective options are not given
    $mode = $getopt['mode'];
    $context = $getopt['context'];

    $dirNames = $getopt->getOperands();

    makeDirectories($dirNames, $createParents, $mode, $context);
} catch (UnexpectedValueException $e) {
    echo "Error: ".$e->getMessage()."\n";
    echo $getopt->getHelpText();
    exit(1);
}

```
