![Hip logo](https://cloud.githubusercontent.com/assets/956212/5879745/0c6f224c-a332-11e4-87a9-4e03b420f865.png)

**H**uman **I**n**p**ut / Simple human readable data markup.

[![Build Status](https://travis-ci.org/mario-deluna/Hip.svg?branch=master)](https://travis-ci.org/mario-deluna/Hip)
[![License](http://img.shields.io/packagist/l/clancats/framework.svg?style=flat)](https://github.com/mario-deluna/Hip)

```yaml
name: "Hip"
type: "Markup language"
version: 1.0
tags: "markup", "serialization", "language"
```

Hip does not try to replace any data markups or create a [new standard](http://xkcd.com/927/). The target of hip is to be readable and writable by non-technicals folks without out having to explain the syntax.

## FAQ

 - **Why should I use this?** Sorry dude I don't know.. This data parser is an experiment and will maybe be implemented into the ClanCatsFramework 2.1. If you are looking for an approved and stable data serialization format use [YAML](http://yaml.org/). If you believe Hip could be useful, feel free, every user makes me happy :)


## Installation 

This Hip parser is written in _PHP_ using _PSR-4_ autoloading you can install it using _composer_. 

```
"require": 
{
    "mario-deluna/hip": "dev-master"
}
```

## Usage 

### Encoding / Decoding

Decode a hip data string to an array:

```php
Hip\Hip::decode( $hipString );
```

Encode an array to a hip data string:

```php
Hip\Hip::decode( $myArray );
```

### Reading / Writing files

Read hip file:

```php
Hip\Hip::read( 'my/path/to/file.hip' );
```

Write hip file:

```php
Hip\Hip::write( 'my/path/to/file.hip', $myArray );
```

## Hip syntax

### Simple key values

```yaml
name: "Zaphod beeblebrox"
job: "President of the Galaxy"
```

wich equals

```json
{
    "name": "Zaphod beeblebrox",
    "job": "President of the Galaxy"
}
```

### Multi layer

```yaml
recipe:
    duration: 60
    ingredients: "eggs", "bacon", "cream", "leek"
```

wich equals

```json
{
    "recipe": 
    {
        "duration": 60,
        "ingredients": [ "eggs", "bacon", "cream", "leek" ]
    } 
}
```

### Array lists

```yaml
instruments:
    -
    name: "Guitar"
    strings: 6
    --
    name: "Bass"
    strings: 4
    -
```

wich equals

```json
{
    "instruments": 
    [
        {
            "name": "Guitar",
            "strings": 6
        },
        {
            "name": "Bass",
            "strings": 4
        }
    ] 
}
```

### data types

```yaml
string: "Hello World"
integer: 42
float: 3.14
yepBool: yes
nopeBool: no
nothing: nil
```

wich equals

```json
{
    "string": "Hello World",
    "integer": 42,
    "float": 3.14,
    "yepBool": true,
    "nopeBool": false,
    "nothing": null
}
```

## TODO 

* Hip config object / utility
* Automatic detect the level indicator ( space, tab etc. )
* more tests...
