---
layout: default
title: Default Values
permalink: /advanced/default-values.html
---
# {{ page.title }}

You can assign a default value to any option that can take an argument. Then if that option is not given at the
command line, it will assume the default value instead of `null`.

```php?start_inline=true
$getopt = new Getopt(array(
    (new Option('n', null, Getopt::REQUIRED_ARGUMENT))
        ->setDefaultValue(10)
));
```

## Alternative

If you want an option to have both a default value and a
[validation function](argument-validation.md), you can set them at the same time
by creating an `Argument` object directly:

```php?start_inline=true
$getopt = new Getopt(array(
    (new Option('n', null, Getopt::REQUIRED_ARGUMENT))
        ->setArgument(new Argument(10, 'is_numeric'))
));
```

## The Legacy Way

If you are using the array notation from Getopt v1, you can set the default value as the fifth
element of the option array. Note that this forces you to set a
[description](option-descriptions.md), though you can leave it blank:

```php?start_inline=true
$getopt = new Getopt(array(
    array('n', null, Getopt::REQUIRED_ARGUMENT, '', 10)
));
```
