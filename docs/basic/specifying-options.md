---
layout: default
title: Specifying Options
permalink: /basic/specifying-options.html
---
# {{ page.title }}

Every option is defined by up to three pieces of information:<

 - its *short name*, which must be a single letter or digit. For instance, if you want to respond to
`php my_program.php -v` by showing some version information, add an option with a short name "v".
 - its *long name*, which must be at least two characters long and can contain letters, digits, underscores
and hyphens, but has to begin with a letter or digit. For instance, you can make *version* the long name
of your `-v` option, which will then also react to `php my_program.php --version`.
 - its *mode*, which describes whether the option has a mandatory, an optional or no argument. The default
is no argument.

**Note:** An option does not need to have both a long and a short name. Either name may be disabled by
setting it to `null`.

Getopt.php offers multiple ways to specify options. Which one you should choose depends on the set of features you
want to use.

## The Original Way (Short Options Only)

This uses the exact same syntax as [PHP's `getopt()` function](http://php.net/manual/en/function.getopt.php)
(and the original GNU getopt). It is the shortest way to set up Getopt, but it does not support
long options or any advanced features:

```php?start_inline=true
$getopt = new Getopt('ab:c::');
```

Each letter or digit in the string declares one option. Letters may be followed by either one or two colons to
determine if the option can or must have an argument:

 - No colon - no argument
 - One colon - argument required
 - Two colons - argument optional

## The Explicit Way

This way has been newly introduced in version 2.0 of Getopt and is the only one that grants access to all advanced
features. It involves manually creating `Option` objects and passing them to the Getopt constructor:

```php?start_inline=true
$getopt = new Getopt(array(
    new Option('a', 'alpha', Getopt::REQUIRED_ARGUMENT),
    new Option(null, 'beta', Getopt::OPTIONAL_ARGUMENT),
    new Option('c', null)
));
```

The three arguments of the `Option` constructor are the same as described above: short name, long name and mode.
Either long or short name can be null. For the mode use one of the three class constants `Getopt::REQUIRED_ARGUMENT`,
`Getopt::OPTONAL_ARGUMENT` or `Getopt::NO_ARGUMENT`. The latter can be omitted as it is the default value.

## The Legacy Way

This is how long names and advanced features could be accessed in Getopt v1. It still works, but is considered
deprecated and will not support future features, thus it should not be used anymore.

This way is very similar to the explicit way, except that the three option arguments are placed in an array:

```php?start_inline=true
$getopt = new Getopt(array(
    array('a', 'alpha', Getopt::REQUIRED_ARGUMENT),
    array(null, 'beta', Getopt::OPTIONAL_ARGUMENT),
    array('c', null)
));
```

## Adding more options

If you want to add options to an existing `Getopt` object, you can do so by calling `addOptions()`.
It takes the same types of arguments as the constructor. If you give it an option with the same name(s) as an already
existing one, the latter will be overwritten. Passing an option where only one name is equal to an existing
option's, but the other one is different, will result in an exception.
