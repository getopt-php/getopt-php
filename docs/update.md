---
layout: default
title: Update GetOpt.PHP
permalink: /update.html
---
# {{ page.title }}

A lot of things have changed since version 2.4 and there where also some breaking changes. Due to this changes you will
need to adjust your code to the new interface.

## Namespace And Class

Not only the namespace changed from `Ulrichsg\Getopt` to `GetOpt` also the class name of the main class changed from
`Getopt` to `GetOpt`. For sure you will need at least one time to change it. If you are using it more often in one file
we suggest to just update the use statement and alias the class name:

```php?start_inline=true
// old:  
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option; 

// new:
use GetOpt\GetOpt as Getopt;
use GetOpt\Option;
```

## Constructor Changed

While the first parameter has still the same meaning and is compatible to version 2, the second parameter is now an
array with settings. To provide the default option mode you have to change it this way:

```php?start_inline=true
// old:
$getOpt = new Getopt([], Getopt::OPTIONAL_ARGUMENT);

// new:
$getOpt = new GetOpt([], [
    GetOpt::SETTING_DEFAULT_MODE => GetOpt::OPTIONAL_ARGUMENT
]);
``` 

## SetBanner And Padding Parameter Removed

The method `Getopt::setBanner()` and the parameter padding for `Getopt::getHelpText()` got removed completely. To
customize the usage message and option table consider reading the section [Help Text]({{ site.baseurl }}/help.html).

Another improvement in this section is the automatic wrapping of text. You can remove line breaks and padding as this
will happen automatically. The width of the console is determined automatically and long text breaks at the end (at
the last space) of the line and the rest is moved to the next line.

```console
$ php myapp.php --help
Usage: example.php [options] [operands]

Options:
  --help     Show this help text.
  -o <arg>   This is a very long description text that wraps at column 80
             because the shell in which this command is executed has only
             80 columns.
  -v         Make the output more verbose
```
