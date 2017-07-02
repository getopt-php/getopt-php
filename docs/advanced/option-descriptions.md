---
layout: default
title: Option Descriptions
permalink: /advanced/option-descriptions.html
---
# {{ page.title }}

You can add short description texts to individual options. They will be used to enhance the
[generated help text]({{ site.baseurl}}/basic/usage-information.html).

```php?start_inline=true
$getopt = new Getopt(array(
    (new Option('v', 'version'))
        ->setDescription('Display version information'),
    (new Option('h', 'help'))
        ->setDescription('Show help text')
));
```

## Argument Names

As of v2.3.0, it is also possible to change the name displayed for the argument in the usage information. This
requires manual creation of an `Argument` object. For instance, the following snippet will make the `-n`
option show up as `-n <count>` (instead of `-n <arg>`):

```php?start_inline=true
$getopt = new Getopt(array(
    (new Option('n', null, Getopt::REQUIRED_ARGUMENT))
        ->setArgument(new Argument(null, null, "count"))
        // see "Argument Validation" for the meaning of the first two parameters
));
```

## The Legacy Way

If you are using the array notation from Getopt v1, you can set the description as the fourth
element of the option array:

```php?start_inline=true
$getopt = new Getopt(array(
   array('v', 'version', Getopt::NO_ARGUMENT, 'Display version information')
));
```
