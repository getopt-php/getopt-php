---
layout: default
title: About
permalink: /
---
# {{ page.title }}

Getopt.php is a command-line argument processor for PHP 5.3 and above. It started out as an object-oriented
replacement for PHP's own <a href="http://php.net/manual/en/function.getopt.php">`getopt()`</a> method,
but has since evolved to become significantly more powerful.

## Feature Overview

 - Supports both short (eg. `-v`) and long (eg. `--version`) options
 - Option aliasing, ie. an option can have both a long and a short version
 - Cumulative short options (eg. `-vvv`)
 - Two alternative notations for long options with arguments: `--option value` and `--option=value`
 - Collapsed short options (eg. `-abc` instead of `-a -b -c`), also with an argument for the last option 
    (eg. `-ab 1` instead of `-a -b 1`)
 - Quoted arguments (eg. `--path "/some path/with spcaces"`)
 - Default argument values
 - Argument validation
