---
layout: default
title: Operands
permalink: /operands.html
---
# {{ page.title }}

Since version 3 it is possible to specify operands. Other than options operands have to be defined and provided in the
correct order. This limitation is because they don't have names.

## Specifying Operands

Operands can be added by `GetOpt::addOperand()` and `GetOpt::addOperands()`. These methods allow only `Operand` and
`Operand[]` respectively. There is no helper to define operands by string or array.

The constructor of `Operand` requires only a name. Optionally you can define the mode for the operand:

| Mode                | Int | Description                                                   |
|---------------------|-----|---------------------------------------------------------------|
| `OPTIONAL`          | 0   | Operand that may or may not appear                            |
| `REQUIRED`          | 1   | Operand that has to appear                                    |
| `MULTIPLE`          | 2   | Operand that can appear multiple times                        |
| `MULTIPLE+REQUIRED` | 3   | Operand that has to appear once but can appear multiple times |

By logic there are some restrictions because of the strict order:

  * a required operand can not follow after optional operands
  * no operand can follow after a multiple operand
  
When you add a required operand after optional operands all previous operands will become required. But when you try
to add an operand after a multiple operand it will throw an `InvalidArgumentException`.

```php?start_inline=true
$getopt = new \GetOpt\GetOpt();
$getopt->addOperand(new \GetOpt\Operand('file', \GetOpt\Operand::REQUIRED));
$getopt->addOperands([
    new \GetOpt\Operand('destination', \GetOpt\Operand::OPTIONAL),
    new \GetOpt\Operand('names', \GetOpt\Operand::MULTIPLE),
]);
```

### Fluent Interface

For convenience there exists a public static method create. So you don't have to wrap your instantiation before you
use other setters. 

### Set up a default value

The default value can be defined the same way as for options. A default value will appear in `GetOpt::getOperands()` as
well as in `GetOpt::getOperand()` and the following example might give an unexpected result for you:

```php?start_inline=true
$getopt = new \GetOpt\GetOpt();
$getopt->addOperands([
    \GetOpt\Operand::create('operand1'),
    \GetOpt\Operand::create('operand2')->setDefaultValue(42),
]);
var_dump($getopt->getOperands()); // [ 42 ]
```

This can lead to a misinterpretation that operand1 is 42 and operand2 is not given. Anyway it is a correct result. If
you are planning such things you should consider using `->getOperand('operand1')` which will return `null`. 

### Validation

Again: it is the same functionality as for validating options. It follows a small example. See 
[Options Validation](options.html#validation) for more details.

```php?start_inline=true
$getopt = new \GetOpt\GetOpt();
$getopt->addOperands([
    \GetOpt\Operand::create('file', \GetOpt\Operand::REQUIRED)
        ->setValidation('is_readable'),
    \GetOpt\Operand::create('destination', \GetOpt\Operand::MULTIPLE)
        ->setValidation(function ($value) {
            return file_exists($value) && is_dir($value) && is_writeable($value); 
        }),
]);
```
