---
layout: default
title: Installation
permalink: /installation.html
---
# {{ page.title }}

The recommended way of installing Getopt.php is to use Composer. Add it to your composer.json file like this:

```json
{
    "require": {
        "ulrichsg/getopt-php": "2.4.*"
    }
}
```

Replace 2.4.* by the release number you want to use (a list of releases is available on 
[Packagist](https://packagist.org/packages/ulrichsg/getopt-php)).

If not using Composer, you can download the desired release from GitHub and integrate it into your application
manually. Getopt.php does not have any external dependencies.
