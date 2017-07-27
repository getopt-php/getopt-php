---
layout: default
title: Options
permalink: /options.html
---
# {{ page.title }}

This page describes the how to specify options and their arguments. It covers everything you need to know to make use
of options.

## Specifying Options

Options are defined by an object of the class `GetOpt\Option`. There are two helpers to create these options.

### Creating Options

You can define options like usual objects:

```php?start_inline=true
$optionAlpha = new \GetOpt\Option('a', 'alpha', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
$optionAlpha->setDescription(
    'This description could be very long ' .
    'and you may want to separate to multiple lines.'
);
$optionAlpha->setArgument(new \GetOpt\Argument(null, 'is_numeric', 'alpha'));
```

And add them to the `GetOpt\GetOpt` object:

```php?start_inline=true
// in constructor
$getopt = new GetOpt([$optionAlpha, $optionBeta]);

// via addOptions
$getopt = new GetOpt();
$getopt->addOptions([$optionAlpha, $optionBeta]);

// via addOption
$getopt = new GetOpt();
$getopt->addOption($optionAlpha)->addOption($optionBeta);
```

### Options From String (Short Options Only)

Options can be defined by a string with the exact same syntax as 
[PHP's `getopt()` function](http://php.net/manual/en/function.getopt.php) and the original GNU getopt. It is the
shortest way to set up GetOpt, but it does not support long options or any advanced features:

```php?start_inline=true
$getopt = new GetOpt('ab:c::');
```

Each letter or digit in the string declares one option. Letters may be followed by either one or two colons to
determine if the option can or must have an argument:

 - No colon - no argument
 - One colon - argument required
 - Two colons - argument optional

### Options From Array

There is also a helper that creates an `GetOpt\Option` from array. These method is de
