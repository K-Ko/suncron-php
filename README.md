# SunCron/PHP

**Generate cron entries from rules based on sunset and/or sunrise**

Project is highly inspired by [6feet5/suncron](https://github.com/6feet5/suncron).

Suncron-php uses a similar rule definition scheme.

## Installation

Just clone the repository with

    cd ~
    git clone https://github.com/K-Ko/suncron-php.git

or download an archive

    cd ~
    wget -qO suncron-php.zip https://github.com/K-Ko/suncron-php/archive/master.zip
    unzip -qqd suncron-php suncron-php.zip
    rm suncron-php.zip

## Usage

The idea is to execute programs depending on sunset or sunrise.

The project is supposed to execute at midnight and calculate todays sunrise and sunset times.
It will use a configuration file with a set of rules and update the cron file with the new values.

Configuration file templates are found in [`dist`](/tree/master/dist). 

A configuration file has a Location section and a rule section.

## Location

#### Latitude
Geographic coordinates for the north–south (-90° ... 90°) position of a point on the Earth's surface.

#### Longitude
Geographic coordinates for the east-west (-180° ... 180°) position of a point on the Earth's surface.

#### Timezone
Calculate the times for the given timezone., will use content of `/etc/timezone` if omitted.

#### Zenith
So sunrise and sunset actually occur when the Sun has altitude -0°50' (34' for refraction, and another 16' for the semi-diameter of the disc).

- `90 + 50/60` (default)

You can also use other "sunrise"/"sunset" definitions...

Since the atmosphere scatters sunlight, the sky does not become dark instantly at sunset, there is a period of twilight.

During **civil twilight**, it is still light enough to carry on ordinary activities out-of-doors. This continues until the Sun's altitude is -6°.

- `Zenith: 96`

During **nautical twilight**, it is dark enough to see the brighter stars,
but still light enough to see the horizon, enabling sailors to measure stellar altitudes for navigation
This continues until the Sun's altitude is -12°. 

- `Zenith: 102`

During **astronomical twilight**, the sky is still too light for making reliable astronomical observations; 
this continues until the Sun's altitude is -18°. 

- `Zenith: 108`

## Rules
Each rule consists of four parts:

#### `if`
Formula describing a logical condition, eg. 'sunrise > 7:00' to test if sun rises before 7:00.

The condition is optional and defaults to `true`if omitted.

#### `then`
#### `else`
Formulas or times to use if the condition is true/false, eg. 'sunrise + 3:00' for three hours after sunrise.
Or you can leave it empty to ignore it.

Both are optional and defaults to `null` if omitted and rule will be skipped.

#### `cron`
A cron line part **without** the minute and hour field, eg. `* * [1-4] root /path/to/command arg`

### Output
The resulting cron data will be writtenby default to a file named like this:
/path/to/**suncron**.yaml > /etc/cron.d/**suncron**

You can redirect the output also to an other file or to stdout with command line switches,
see `./suncron.php --help` for reference.

## Run

Best run SunCron/PHP at or shortly after midnight.

**Always** run SunCron/PP as `root` user, script must write to file(s) in `/etc/cron.d/`!

Either via root crontab ( `sudo crontab -e` )

    0 * * * /path/to/suncron.php /path/to/config.yaml

or with an own cron file in `/etc/cron.d/`

    echo '0 * * * root /path/to/suncron.php /path/to/config.yaml' | sudo tee /etc/cron.d/suncron-php

## Command line

```
* * *  SunCron/PHP v0.1.0 (2017-04-03)  * * *

SUMMARY
    ./suncron.php -- Create crontabs relative to sunrise and sunset.

USAGE
    ./suncron.php [options] <config file>

DESCRIPTION
    See dist/*.yaml for examples and details
    Github: https://github.com/K-Ko/suncron-php

OPTIONS
    -s
    --stdout
        Write cron entries to stdout.

    -o <value>
    --output=<value>
        Write cron entries to file <value>.

    -t
    --test
        Test mode, only analyse configuration

    -v [-v [...]]
        Verbosity level, multiple use increases verbosity

    --help
        This help
```

