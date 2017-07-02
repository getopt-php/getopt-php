---
layout: default
title: Retrieving Values
permalink: /basic/retrieving-values.html
---
# {{ page.title }}

After you have constructed your `Getopt` object and specified all your options, you have to invoke the parser
next. In the usual case this looks just like this:

```php?start_inline=true
$getopt->parse();
```

By default, Getopt looks for command line arguments in the superglobal variable `$GLOBALS['argv']`. You can override
this behavior by passing a string or array.

After that (and if no error occurred - see below for that case) you can use the following methods:

## Getting Option Values

You can get an individual option's value by its name or all given options as an associative array with names as keys:

```php?start_inline=true
$getopt->getOption('v');
$getopt->getOptions();

// alternative: syntactic sugar
$getopt['v'];
```

An option that has both a short and long name can be retrieved using either, regardless of which one was actually given
at the command line.

The possible option values are:

 - If the option was given with an argument (for the `REQUIRED_ARGUMENT` and `OPTIONAL_ARGUMENT` modes),
the value is that argument, as a string.
 - If the option was given without an argument (for the `OPTIONAL_ARGUMENT` and `NO_ARGUMENT` modes),
the value is the number of times the option occurred on the command line. This allows for cumulative options,
like many *nix tools use for e.g. verbosity levels: a command line like `-vvv` results in a value of&nbsp;3.
 - If the option was not given, the value is `null`.

## Getting Operand Values

Everything that is not an option or option argument is considered an operand. In addition, if the command line contains
the string ` -- `, everything after it is also treated as an operand. These can be retrieved individually
by index or as an array:

```php?start_inline=true
// returns the ($i+1)th operand as a string or null if there are less operands
$getopt->getOperand($i);

// returns all operands
$getopt->getOperands();
```

## Error Handling

If the command line arguments do not match the specified options, `Getopt::parse()` will throw a standard
`UnexpectedValueException` with a message containing detailed information about what went wrong. Catching
this exception thus allows you to react to the error the way you prefer (you could, for instance, use
`Getopt::getHelpText()` to print [usage information](usage-information.md)).
