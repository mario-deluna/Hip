# Hip

**H**uman **I**n**p**ut

Guys please don't ask me: "Why should I use this?". The answer will always be: "I don't know sorry.". This data parser is more an experiment and will maybe be implemented into the ClanCatsFramework 2.1. If you guys are searching for a nice tested and approved data Serialization format use [YAML](http://yaml.org/).

If you still would like to try "HIP", feel free :)

[![Build Status](https://travis-ci.org/mario-deluna/Hip.svg?branch=master)](https://travis-ci.org/mario-deluna/Hip)

## TODO 

 * The encode function
 * Hip config util
 * Unit tests
 * Not only tab's as level indicator

## Installation 

This Hip parser is written in _PHP_ using _PSR-4_ autoloading you can install it using _composer_. 

```
"require": 
{
    "mario-deluna/hip": "dev-master"
}
```

## Syntax

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