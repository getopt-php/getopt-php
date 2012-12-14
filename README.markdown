Getopt.PHP
==========

Getopt.PHP allows for easy processing of command-line arguments. It is a more powerful, object-oriented
alternative to PHP's built-in [`getopt()`](http://php.net/manual/en/function.getopt.php) function.


Features
--------

* Supports both short (eg. `-v`) and long (eg. `--version`) options
* Option aliasing, ie. an option can have both a long and a short version
* Collapsed short options (eg. `-abc` instead of `-a -b -c`)
* Cumulative options (eg. `-vvv`)
* Options may take optional or mandatory arguments
* Two alternative notations for long options with arguments: `--option value` and `--option=value`


Usage
-----
### 0. Include the package

```php
require 'vendor/autoload.php';
use Ulrichsg\Getopt;
```
### 1. Create a Getopt object

There are two ways to construct a Getopt instance:

#### Short options only

The first way uses the very compact notation used by PHP's `getopt()` (and the original GNU getopt),
but cannot be used to declare long options:
```php
$getopt = new Getopt('ab:c::');
```
Each letter declares one option. Letters may be followed by either one or two colons to determine
if the option can or must have an argument:

* No colon - no argument
* One colon - argument required
* Two colons - argument optional

#### Short and long options

The second way is used to harness the full power of Getopt.PHP. In this case, the argument passed to
the constructor must be an array of arrays. Each of the inner arrays represents one option and must
have exactly three fields, in this order:

* The option's short name, or `null` if the option should only have a long name
* The option's long name, or `null` if the option should only have a short name (note that it is not
  permitted to set both names to `null`)
* The option's argument mode. Getopt defines three constants for use here: `Getopt::NO_ARGUMENT`,
  `Getopt::OPTIONAL_ARGUMENT` and `Getopt::REQUIRED_ARGUMENT`.

Example:

```php
$getopt = new Getopt(array(
    array('a', null, Getopt::NO_ARGUMENT),
    array(null, 'bravo', Getopt::REQUIRED_ARGUMENT),
    array('c', 'charlie', Getopt::OPTIONAL_ARGUMENT)
));
```

#### Adding more options after the Getopt object has been created

The method `addOptions()` can be called with the same arguments as `__construct()`, the options that
get parsed, will be merged with the previous ones.

```php
$getopt = new Getopt;
$getopt->addOptions('ab:c::')
$getopt->addOptions(array(
    array('a', null, Getopt::NO_ARGUMENT),
    array(null, 'bravo', Getopt::REQUIRED_ARGUMENT),
    array('c', 'charlie', Getopt::OPTIONAL_ARGUMENT)
));
```

#### Description

You can optionally pass descriptions to arguments

```php
$getopt = new Getopt(array(
	array('a', 'alpha', Getopt::NO_ARGUMENT, 'Short and long options with no argument'),
	array(null, 'beta', Getopt::OPTIONAL_ARGUMENT, 'Long option only with an optional argument'),
	array('c', null, Getopt::REQUIRED_ARGUMENT, 'Short option only with a mandatory argument')
));
```

Which can then be used by `showHelp()` to print a help message

```bash
Usage: script.php [options] [operands]
Options:
  -a, --alpha             Short and long options with no argument
  --beta [<arg>]          Long option only with an optional argument
  -c <arg>                Short option only with a mandatory argument
```

### 2. Invoke the parser

After constructing the Getopt object, a call to `parse()` will evaluate the arguments and store the
result for retrieval. `parse()` can be invoked with or without argument. In the usual case where you
want to parse the calling script's command-line arguments (in the global PHP variable `$argv`), the
argument can be omitted. Passing any string or array to the method will make it interpret that
value instead.

### 3. Retrieve the values

Getopt.PHP has two methods for retrieving data: `getOption()` and `getOperands()`.

#### getOption

`$getopt->getOption($name)` returns the value associated with the option `name`. The value can be
one of the following:

* `null`, if the option does not occur in the parsed arguments
* an integer, if the option occurs without argument. The actual value is the number of occurrences.
  In most cases this will be 1, only in case of a cumulative option it can be greater than that (eg.
  for `-vvv` a call to `getOption('v')` will return 3).
* a string, if the option occurs with an argument. The actual value is, of course, that argument.

Note that, if an option has both a short and a long name, it can be retrieved using either name
regardless of which name is used in the parsed data:

```php
$getopt = new Getopt(array(
    array('o', 'option', Getopt::REQUIRED_ARGUMENT)
);
$getopt->parse('-o value');
echo $getopt->getOption('option')); // value
```

#### getOperands

`$getopt->getOperands()` returns the (possibly empty) array of operands. Operands are arguments that
are neither options nor option values. Getopt determines the operands using the following rules:

* If a double hyphen `--` occurs in the list of arguments, everything after it is considered an
  operand.
* If an argument is encountered that does not start with a hyphen, but cannot be an option value
  (because the preceding option does not support arguments or already has a value, or because it
  is the first argument), then it and everything after it is considered an operand.
* If an argument is encountered that starts with one or two hyphens, but is not a known option,
  it is *not* considered an operand, but an error is thrown.


Error handling
--------------

Getopt.PHP uses two types of exceptions (both from the PHP standard library) to indicate errors.
In both cases, the exception's message contains details about the exact cause of the error.

* `InvalidArgumentException` is thrown when the argument passed to the constructor is not well-formed.
* `UnexpectedValueException` is thrown when the argument list processed by `parse()` is not
  well-formed, or does not conform to the declared options.


Notes
-----

* Short option names must be letters from the set [A-Za-z]. Long option names may also contain
  digits, hyphens and underscores.
* Avoid option values that start with a hyphen. For instance, an argument string such as `-a -b`
  is always interpreted as two separate options `a` and `b`, never as an option `a` with the value
  `-b`. The only valid way to pass option values starting with a hyphen is to use long options with
  an equals sign: `--option=-value` works, whereas `--option -value` does not.


Composer support
----------------

Getopt.PHP is available as a [Composer](https://github.com/composer/composer) package on
[Packagist](http://packagist.org/packages/ulrichsg/getopt-php).


References
----------

* [GNU getopt documentation](https://www.gnu.org/s/hello/manual/libc/Getopt.html)
* [PHP function reference: getopt](http://php.net/manual/en/function.getopt.php)


License
-------

Getopt.PHP is (c) 2011 Ulrich Schmidt-Goertz. It is published under the
[MIT License](http://www.opensource.org/licenses/mit-license.php).
