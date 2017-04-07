# SunCron/PHP

**Generate cron entries from rules based on sunset and/or sunrise**

The idea is to execute programs depending on sunset or sunrise.

The project is supposed to execute at midnight and calculate todays sunrise and sunset times.
It will use a configuration file with a set of rules and update a cron file with the new values.

Project is highly inspired by [6feet5/suncron](https://github.com/6feet5/suncron)
and uses a similar rule definition scheme.


## Installation

### Repository

Just clone the repository

    cd ~
    git clone https://github.com/K-Ko/suncron-php.git

or download an archive

    cd ~
    wget -qO suncron-php.zip https://github.com/K-Ko/suncron-php/archive/master.zip
    unzip -qqd suncron-php suncron-php.zip
    rm suncron-php.zip

SunCron/PHP uses some other repositories via [Composer](https://getcomposer.org/).

    cd suncron-php
    composer -a --apcu-autoloader update

### Global

There is also a very simple Makefile which

- Links `suncron.php` to `/usr/bin/suncron`
- Copies the example configuration files to `/etc/default`
- Installs a daily cron job /etc/cron.d`/suncron-daily` with
  `10 0 * * * root /usr/bin/suncron /etc/default/suncron.yaml`

To install run

    make install

Remove with

    make uninstall

## Usage

Configuration file templates are found in [`dist`](tree/master/dist).

A configuration file has a Location section, an optional Environment section and a rule section.

## Location

#### Latitude
Geographic coordinates for the north–south (-90° ... 90°) position of a point on the Earth's surface.

#### Longitude
Geographic coordinates for the east-west (-180° ... 180°) position of a point on the Earth's surface.

#### Timezone
Calculate the times for the given timezone., will use content of `/etc/timezone` if omitted.

#### Zenith
So sunrise and sunset actually occur when the Sun has altitude -0°50' (34' for refraction, and another 16' for the semi-diameter of the disc), aka 90+50/60.

> `Zenith: 90.83333333333333` (default)

You can also use other "sunrise"/"sunset" definitions...

Since the atmosphere scatters sunlight, the sky does not become dark instantly at sunset, there is a period of twilight.

During **civil twilight**, it is still light enough to carry on ordinary activities out-of-doors. This continues until the Sun's altitude is -6°.

> `Zenith: 96`

During **nautical twilight**, it is dark enough to see the brighter stars,
but still light enough to see the horizon, enabling sailors to measure stellar altitudes for navigation
This continues until the Sun's altitude is -12°.

> `Zenith: 102`

During **astronomical twilight**, the sky is still too light for making reliable astronomical observations;
this continues until the Sun's altitude is -18°.

> `Zenith: 108`

## Environement
If you need special environment setting, define them here.

> `VARIABLE: value`

## Rules
Each rule consists of up to eight parts:

#### `if`
Formula describing a logical condition

> `if: sunrise > 7:00` # test if sun rises before 7:00

The condition is optional and defaults to `true` if omitted.

#### `then`, `else`
Formulas or times to use if the condition is true/false

> `then: sunrise + 3:00` # three hours after sunrise

You can leave then empty to ignore them.

Both are optional and defaults to `null` if omitted and the rule will be skipped.

#### `day`, `month`, `dow`
The 3rd to 5th field (day, month and day of week) of a crontab entry.

See `man 5 crontab` or eg. [Wikipedia](https://en.wikipedia.org/wiki/Cron#CRON_expression) for syntax help.

They defaults all to `*`.

#### `user`
The user which will run the command, defaults to `root`.

#### `cmd`
The command to run via cron.

Required, missing command will raise an exception.

### Output
The resulting cron data will be written by default to a file named like this:

> Configuration file '/path/to/**suncron**.yaml' will result in '/etc/cron.d/**suncron**'

You can redirect the output also to an other file or to stdout with command line switches.

## Run

Best run SunCron/PHP at or shortly after midnight.

**Always** run SunCron/PHP as `root` user, script must write to file(s) in `/etc/cron.d/`!

Either via root crontab ( `sudo crontab -e` )

    10 0 * * /path/to/suncron.php /path/to/config.yaml

or with an own cron file in `/etc/cron.d/`.
