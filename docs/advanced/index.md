---
layout: default
title: Advanced Features
permalink: /advanced/
---
# {{ page.title }}

These optional features add more power to Getopt in exchange for some additional configuration work.

One thing all of these have in common is that they make use of chainable methods. That makes it easy to add any
number of them to an `Option` object, for example (PHP 5.4 and above only):

```php?start_inline=true
$getopt = new Getopt(array(
    (new Option('o', 'option', Getopt::REQUIRED_ARGUMENT))   // note the extra set of parentheses
        ->setDescription('Description of option')
        ->setDefaultValue('default')
));
```

As of Getopt 2.2, users restricted to PHP 5.3 (where chaining methods directly off the constructor is not supported
yet) can use the static `create()` method instead:

```php?start_inline=true
$getopt = new Getopt(array(
    Option::create('o', 'option', Getopt::REQUIRED_ARGUMENT)
        ->setDescription('Description of option')
        ->setDefaultValue('default')
));
```

## Topics

**[Option Descriptions](option-descriptions.md)** - Add description texts to options for richer help texts

**[Default Values](default-values.md)** - Set default values for option arguments 

**[Argument Validation](argument-validation.md)** - Automatically check the validity of option arguments
