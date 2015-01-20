# Hip

[![Build Status](https://travis-ci.org/mario-deluna/Hip.svg?branch=master)](https://travis-ci.org/mario-deluna/Hip)

**H**uman **I**n**p**ut

Hip does not try to replace any data markups or create a [new standard](http://xkcd.com/927/). The target of hip is to be readable and writable by non-technicals folks without out having to explain the syntax.

## FAQ

 - **Why should I use this?** Sorry dude I don't know.. This data parser is an experiment and will maybe be implemented into the ClanCatsFramework 2.1. If you are looking for a approved and stable data serialization format use [YAML](http://yaml.org/). If you believe Hip could be useful for you feel free, every user makes me happy :)


## TODO 

 * An encoding function
 * Hip config object / utility
 * Automatic detect the level indicator ( space, tab etc. )
 * more tests...

## Installation 

This Hip parser is written in _PHP_ using _PSR-4_ autoloading you can install it using _composer_. 

```
"require": 
{
    "mario-deluna/hip": "dev-master"
}
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

### Lists

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

Let an example speek:

```
bands:
    # Some people hate them some love them
    -
    name: "La Dispute"
    active: yes
    genre: "Post-Hardcore", "Progressive-Rock"
    albumCount: 3
    members:
        -
        name: "Jordan Dreyer"
        role: "Vocalist"
        --
        name: "Brad Vander Lugt"
        role: "Drummer"
        --
        name: "Chad Sterenberg"
        role: "Guitarist"
        --
        name: "Adam Vass"
        role: "Bass Guitarist"
        -
    --
    
    # I saw them live in Berlin. It was an 
    # amazing concert!
    name: "Muse"
    active: yes
    genre: "Alternative rock", "New Prog"
    members:
        -
        name: "Matthew Bellamy"
        role: 
            "Vocalist"
            "Guitarist"
            "Pianist"
        --
        name: "Dominic Howard"
        role: "Drummer"
        --
        name: "Christopher Wolstenholme"
        role: "Bass Guitarist"
        -
    -
```

which will be parsed to:

```
Array
(
    [bands] => Array
        (
            [0] => Array
                (
                    [name] => La Dispute
                    [active] => 1
                    [genre] => Array
                        (
                            [0] => Post-Hardcore
                            [1] => Progressive-Rock
                        )

                    [albumCount] => 3
                    [members] => Array
                        (
                            [0] => Array
                                (
                                    [name] => Jordan Dreyer
                                    [role] => Vocalist
                                )

                            [1] => Array
                                (
                                    [name] => Brad Vander Lugt
                                    [role] => Drummer
                                )

                            [2] => Array
                                (
                                    [name] => Chad Sterenberg
                                    [role] => Guitarist
                                )

                            [3] => Array
                                (
                                    [name] => Adam Vass
                                    [role] => Bass Guitarist
                                )

                        )

                )

            [1] => Array
                (
                    [name] => Muse
                    [active] => 1
                    [genre] => Array
                        (
                            [0] => Alternative rock
                            [1] => New Prog
                        )

                    [members] => Array
                        (
                            [0] => Array
                                (
                                    [name] => Matthew Bellamy
                                    [role] => Array
                                        (
                                            [0] => Vocalist
                                            [1] => Guitarist
                                            [2] => Pianist
                                        )

                                )

                            [1] => Array
                                (
                                    [name] => Dominic Howard
                                    [role] => Drummer
                                )

                            [2] => Array
                                (
                                    [name] => Christopher Wolstenholme
                                    [role] => Bass Guitarist
                                )

                        )

                )

        )

)
```