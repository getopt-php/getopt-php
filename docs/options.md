---
layout: default
title: Options
permalink: /options.html
---
# {{ page.title }}

This page describes how to specify options and their arguments. It covers everything you need to know to make use of
options.

## Specifying Options

Options are defined by an object of the class `GetOpt\Option`. There are two helpers to create these options but we
recommend to use the usual way to create objects.

> We are using argument definition in these examples have a look at [specifying arguments](#specifying-arguments) to
> learn more about it.

### Creating Options

```php
<?php
$optionAlpha = new \GetOpt\Option('a', 'alpha', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
$optionAlpha->setDescription(
    'This description could be very long ' .
    'and you may want to separate to multiple lines.'
);
$optionAlpha->setValidation('is_numeric');
```

And add them to the `GetOpt\GetOpt` object:

```php
<?php
// in constructor
$getopt = new GetOpt([$optionAlpha, $optionBeta]);

// via addOptions
$getopt = new GetOpt();
$getopt->addOptions([$optionAlpha, $optionBeta]);

// via addOption
$getopt = new GetOpt();
$getopt->addOption($optionAlpha)->addOption($optionBeta);
```

The setters can be chained and for convenience there is also a public static method create which allows to write the 
above command this way:

```php
<?php
$getopt = new \GetOpt\GetOpt([
    
    \GetOpt\Option::create('a', 'alpha', \GetOpt\GetOpt::REQUIRED_ARGUMENT)
        ->setDescription('This is the description for the alpha option')
        ->setArgument(new \GetOpt\Argument(null, 'is_numeric', 'alpha')),
    
    \GetOpt\Option::create('b', 'beta', \GetOpt\GetOpt::NO_ARGUMENT)
        ->setDescription('This is the description for the beta option'),
        
]);
```

> This looks very clean in my opinion

### Options From String (Short Options Only)

Options can be defined by a string with the exact same syntax as 
[PHP's `getopt()` function](http://php.net/manual/en/function.getopt.php) and the original GNU getopt. It is the
shortest way to set up GetOpt, but it does not support long options or any advanced features:

```php
<?php
$getopt = new GetOpt('ab:c::');
```

Each letter or digit in the string declares one option. Letters may be followed by either one or two colons to
determine if the option can or must have an argument:

 - No colon - no argument
 - One colon - argument required
 - Two colons - argument optional

### Options From Array

There is also a helper that creates an `GetOpt\Option` from array. These method allows the most important options and
can look very clean too:

```php
<?php
$getopt = new \GetOpt\GetOpt([
   
    // creates a short option a without a long alias and with the default argument mode
    ['a'],
    
    // creates a short option wihout a short alias and with the default argument mode
    ['beta'],
    
    // you can define the argument mode
    ['c', \GetOpt\GetOpt::REQUIRED_ARGUMENT],
    
    // you can define long, short, argument mode, description and default value
    ['d', 'delta', \GetOpt\GetOpt::MULTIPLE_ARGUMENT, 'Description for delta', 'default value'],
    
    // note that you have to provide null values if you want to add a desciprtion or default value
    ['e', null, \GetOpt\GetOpt::NO_ARGUMENT, 'Enable something'],
    
]);
```

This method does not allow to specify the validation or the argument name but you can get the option and define it
afterwards:

```php
<?php
$getopt->getOption('beta', true)
    ->setDescription('Provide a beta version')
    ->setMode(\GetOpt\GetOpt::OPTIONAL_ARGUMENT)
    ->setArgument(new \GetOpt\Argument(null, null, 'beta version'));
```

## Specifying Arguments

