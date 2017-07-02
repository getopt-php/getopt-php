# Getopt.PHP

[![Build Status](https://travis-ci.org/getopt-php/getopt-php.svg?branch=master)](https://travis-ci.org/getopt-php/getopt-php)
[![Coverage Status](https://coveralls.io/repos/github/getopt-php/getopt-php/badge.svg?branch=master)](https://coveralls.io/github/getopt-php/getopt-php?branch=master)
[![Latest Stable Version](https://poser.pugx.org/ulrichsg/getopt-php/v/stable.svg)](https://packagist.org/packages/ulrichsg/getopt-php) 
[![Total Downloads](https://poser.pugx.org/ulrichsg/getopt-php/downloads.svg)](https://packagist.org/packages/ulrichsg/getopt-php) 
[![License](https://poser.pugx.org/ulrichsg/getopt-php/license.svg)](https://packagist.org/packages/ulrichsg/getopt-php)

Getopt.PHP is a library for command-line argument processing. It supports PHP version 5.3 and above.

## Features

* Supports both short (eg. `-v`) and long (eg. `--version`) options
* Option aliasing, ie. an option can have both a long and a short version
* Collapsed short options (eg. `-abc` instead of `-a -b -c`)
* Cumulative options (eg. `-vvv`)
* Options may take optional or mandatory arguments
* Two alternative notations for long options with arguments: `--option value` and `--option=value`
* Collapsed short options with mandatory argument at the end (eg. `-ab 1` instead of `-a -b 1`)
* Quoted arguments (eg. `--path "/some path/with spcaces"`)

## Documentation

* [Documentation for the current version (2.0+)](http://getopt-php.github.io/getopt-php/)
* [Legacy documentation (1.4)](https://github.com/getopt-php/getopt-php/blob/1.4.1/README.markdown)

## License

Getopt.PHP is published under the [MIT License](http://www.opensource.org/licenses/mit-license.php).
