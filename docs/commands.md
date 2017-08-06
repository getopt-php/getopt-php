---
layout: default
title: Commands
permalink: /commands.html
---
# {{ page.title }}

The concept behind commands is a single entry with different tasks. For example an administration backend with the
option to create, read, update and delete users. Instead of defining a getopt with many optional options that later 
getting required or need a different validation (username has to exists for update and delete, but has to be unique for
create) we create commands.

## Defining Commands

A command can has at least a name and a handler. The handler can be anything that makes clear what has to be executed
(a `colsure` makes sense, but an array `['Controller', 'method']` too).

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addCommand(new \GetOpt\Command('create', 'User::create'));
```

### Setup Description

There are two descriptions - the short version is shown in the list of commands while the long description is by
default shown when a command is given:

```console
$ php program.php --help
Usage: program.php <command> [options] [operands]
Options:
  -h --help  Shows this help
Commands:
  setup  Short description of setup
$ php program.php setup --help
Usage: program.php setup [options] [operands]

This is a longer description of the command.

It may describe in more details what happens when you call it.

Options:
  -h --help    Shows this help
  -o --option  An option from the setup command
```

You can only define one description that is used for both or you define both descriptions:

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('setup', 'Setup::setup')
        ->setDescription('Setup the application'),
        
    \GetOpt\Command::create('user:create', 'User::create')
        ->setDescription(
            'Creates a new user with the given data.' . PHP_EOL .
            PHP_EOL .
            'You can omit username and password when you use interactive mode.'
        )->setShortDescription('Create a new user'),
]);
```

### Command Specific Options

A command can have specific options. Like for `GetOpt` you can pass the options through constructor or using the
methods `addOption(Option)` and `addOptions(Option[])`.

```php
<?php
$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('user:delete', 'User::delete', [
        \GetOpt\Option::create('u', 'userId', \GetOpt\GetOpt::REQUIRED_ARGUMENT),
    ]),
    
    \GetOpt\Command::create('user:create', 'User::create')
        ->addOptions([
            \GetOpt\Option::create('u', 'username', \GetOpt\GetOpt::REQUIRED_ARGUMENT),
            \GetOpt\Option::create('p', 'password', \GetOpt\GetOpt::REQUIRED_ARGUMENT),
            \GetOpt\Option::create('i', 'interactive'),
        ]),
]);
```

You can also reuse the options and share options for different commands:

```php
<?php
/** @var \GetOpt\Option[] $options */
$options = [];
$options['userId'] = \GetOpt\Option::create('u', 'userId', \GetOpt\GetOpt::REQUIRED_ARGUMENT);
$options['interactive'] = \GetOpt\Option::create('i', 'interactive');

$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('user:delete', 'User::delete')
        ->addOption($options['userId']),
        
    \GetOpt\Command::create('user:edit', 'User::edit')
        ->addOptions([
            $options['interactive'],
            $options['userId']
        ]),
]);
```

### Command Specific Operands

You can specify operands that are only valid for a specific command the same way as for `GetOpt`. Also you can reuse
these Operands for different commands.

```php
<?php
$operandUserId = \GetOpt\Operand::create('userId', \GetOpt\Operand::MULTIPLE);

$getopt = new \GetOpt\GetOpt();
$getopt->addCommands([
    \GetOpt\Command::create('user:delete', 'User::delete')->addOperand($operandUserId),
    
    \GetOpt\Command::create('user:export', 'User::export')->addOperand($operandUserId),
]);
```

### Limitations

#### A command can not specify an option that is already defined "globally"
 
`GetOpt` will throw an exception if you try to add a command with an option that conflicts with another option. You
could anyway first add the command and later add the option. But anyway it will throw an exception when the command is
getting executed. We suggest first to add common options and later commands.

#### Command must be set before operands

This is an artificial limitation. The command has to be the first operand. When you add common operands these will be
the first operands after the command and followed by command specific operands. We suggest not to do so and don't add
common operands.

## Working With Commands

After processing the command line arguments we can receive the current command with `GetOpt::getCommand()` without a
parameter. It returns the Command object and we can use the getters `Command::name()`, `Command::handler()`,
`Command::description()` and `Command:shortDescription()` to identify the command. If no command is specified it will
return `null`.

```php
<?php
$getopt = new \GetOpt\GetOpt();
// define options and commands

try {
    $getopt->process();
} catch (\GetOpt\ArgumentException $exception) {
    // do something with this exception
}

$command = $getopt->getCommand();
if (!$command) {
    // no command given - show help?
} else {
    // do something with the command - example:
    list ($class, $method) = explode('::', $command->handler());
    $controller = makeController($class);
    call_user_func([$controller, $method], $getopt->getOptions(), $getopt->getOperands());
}
```
