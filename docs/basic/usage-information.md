---
layout: default
title: Usage Information
permalink: /basic/usage-information.html
---
# {{ page.title }}

Getopt can automatically generate a human-readable usage information from the specified options. Use it for quickly
setting up help texts:

```php?start_inline=true
echo $getopt->getHelpText();
```

The result will look similar to this:

```
Usage: my-program.php [options] [operands]
Options:
  -a, --alpha             Short and long options with no argument
  --beta [<arg>]          Long option only with an optional argument
  -c <arg>                Short option only with a mandatory argument
```

**Notes:**

 - The name of the program is determined automatically.
 - The description text following each option only appears if you have set it
(<a href="{{ site.baseurl}}/advanced/option-descriptions.html">see here</a>).
 - You can change the padding of the description texts by passing the desired number to `getHelpText()`.
The default is 25.

## Customizing the Message

You can change the first line of the help text to whatever you want like this:

```php?start_inline=true
$getopt->setBanner("My custom help message for %s\n");
```

The (optional) placeholder `%s` will be replaced by the program name.
