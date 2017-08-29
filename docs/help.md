---
layout: default
title: Help Text
permalink: /help.html
---
# {{ page.title }}

This library can generate console output that helps your users to understand how they can use your application.
The output varies depending on what options, operands and commands you provide, if additional operands and
custom options are allowed, and so on.

## Customizing Help

By default, `GetOpt::getHelpText()` uses the `GetOpt\Help` class that implements `GetOpt\HelpInterface`.

You can provide your own, custom help text generator with `GetOpt::setHelp(HelpInterface)`.
The method `HelpInterface::render(GetOpt, array)` receives the `GetOpt` object from which
`getHelpText()` was called, with additional custom data in the second parameter.

### Custom Templates

Instead of developing your own custom Help class, you may also copy and modify
the default templates under `resources/*.php`. The output from these templates
is used to generate the help text.

For a better understanding of what is happening, you should have a look at
the [source code of the `GetOpt\Help` class](https://github.com/getopt-php/getopt-php/blob/master/src/Help.php).

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->getHelp()
    ->setUsageTemplate('path/to/my/usageTemplate.php')
    ->setOptionsTemplate('path/to/my/optionsTemplate.php')
    ->setCommandsTemplate('path/to/my/commandsTemplate.php');
```

In the following sections, you will find a complete description of the three
templates, and what they are showing by default.

#### Usage

The _usage_ briefly describes how to run your application (i.e. the command's syntax).
It shows the script name, if a command has to be given,
where the options should be entered and the name and order of operands.

 - Default with commands and options defined:
   `Usage: path/to/app <command> [options] [operands]`
 - Command `make:config` is given, options are defined, strict operands with operand `file` defined:
   `Usage: path/to/app make:config [options] <file>`
 - No commands, options and operands defined and strict operands:
   `Usage: path/to/app`

#### Options

Options are shown in a table with the options (including argument) in the left column, and
the description of each option in the right column.

Long descriptions automatically break after the last space that fits into the
terminal's width. The number of columns is determined in the following sequence:

1. a constant `COLUMNS`,
2. an environment variable `COLUMNS`,
3. the result from `tput cols` command
4. value `90`

This is limited by `$maxWidth` or `120` if not defined.

In the end it might look something like this:

```
Options:
  -h --help           Shows this help
  -c --config <file>  Use this configuration file. By default the configuration from user
                      is used (e. g. $HOME/.myapp.php.inc)
  --version           Show version information and quit
```

#### Commands

Commands are shown in a table similar to options. Because they only have a name the list might look something
like this:

```
Commands:
  user:create    Create a new user
  user:delete    Delete an existing user
  user:edit      Edit the information of an user
  user:password  Set a new password for the user. Alternative you can also send a link to
                 change the password to his current eMail address.
```

The list of commands is only shown when at least one command is defined, and no command is set. When a command is set, the
options, operands and the long description from the command is shown:

```console
$ ./app user:create --help
Usage: ./app user:create [options] [<username>]

Create a new user.

When the username is omitted you will be prompted for a username.

Options:
  -h --help           Shows this help
  -c --config <file>  Use this configuration file. By default the configuration from user
                      is used (e. g. $HOME/.myapp.php.inc)
  --version           Show version information and quit
  --password <arg>    The password for the user
  --email <arg>       The email address for the user
  --no-interaction    Throw an error when data is missing
```
