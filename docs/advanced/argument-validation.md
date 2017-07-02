---
layout: default
title: Argument Validation
permalink: /advanced/argument-validation.html
---
# {{ page.title }}

You can have Getopt check the validity of an option argument by assigning it a
[callable](http://www.php.net/manual/en/language.types.callable.php) validation function:

```php?start_inline=true
$getopt = new Getopt(array(
    (new Option('a', null, Getopt::REQUIRED_ARGUMENT))
        ->setValidation(function($value) {
            return ($value > 9000);
        })
));
```

If the validation returns `false`, an `UnexpectedValueException` is thrown. This is the same type
thrown for any other errors related to user input, so you don't have to handle it separately.

## Alternative

If you want an option to have both a validation function and a
[default value](default-values.md), you can set them at the same time
by creating an `Argument` object directly:

```php?start_inline=true
$getopt = new Getopt(array(
    (new Option('n', null, Getopt::REQUIRED_ARGUMENT))
        ->setArgument(new Argument(10, 'is_numeric'))
));
```
