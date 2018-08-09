# SunCron/PHP

## Generate cron entries from rules based on sunset and/or sunrise

The idea is to execute programs depending on sunset or sunrise.

The project is supposed to execute at midnight and calculate todays sunrise and
sunset times. It will use a configuration file with a set of rules and update
a cron file with the new values.

Project is highly inspired by [6feet5/suncron](https://github.com/6feet5/suncron)
and uses a similar rule definition scheme.

## Installation

### Repository

    cd ~
    git clone https://github.com/K-Ko/suncron-php.git

### Download

    cd ~
    wget -qO suncron-php.zip https://github.com/K-Ko/suncron-php/archive/master.zip
    unzip -qqd suncron-php suncron-php.zip
    rm suncron-php.zip

### Prepare dependencies

SunCron/PHP uses some other repositories via [Composer](https://getcomposer.org/).

    cd suncron-php
    composer -o --no-dev update

### Global install

There is also a very simple `Makefile` to

- Link `suncron.php` to `/usr/bin/suncron-php`
- Copy the example configuration files to `/etc/suncron-php`

To install run

    sudo make install

Remove with

    sudo make uninstall

# Usage

Configuration file templates are found in
[`dist`](https://github.com/K-Ko/suncron-php/tree/master/dist).

A configuration file has a Location section, an optional Environment section
and a rule section.

## Location

### Latitude
Geographic coordinates for the north–south (-90° ... 90°) position of a point
on the Earth's surface.

### Longitude
Geographic coordinates for the east-west (-180° ... 180°) position of a point
on the Earth's surface.

### Timezone
Calculate the times for the given timezone., will use content of `/etc/timezone`
if omitted.

### Zenith
So sunrise and sunset actually occur when the Sun has altitude -0°50'
(34' for refraction, and another 16' for the semi-diameter of the disc),
aka 90+50/60.

> `Zenith: 90.83333333333333` (default)

You can also use other "sunrise" / "sunset" definitions...

Since the atmosphere scatters sunlight, the sky does not become dark instantly
at sunset, there is a period of twilight.

During **civil twilight**, it is still light enough to carry on ordinary
activities out-of-doors. This continues until the Sun's altitude is -6°.

> `Zenith: 96`

During **nautical twilight**, it is dark enough to see the brighter stars,
but still light enough to see the horizon, enabling sailors to measure stellar
altitudes for navigation; this continues until the Sun's altitude is -12°.

> `Zenith: 102`

During **astronomical twilight**, the sky is still too light for making reliable
astronomical observations; this continues until the Sun's altitude is -18°.

> `Zenith: 108`

## Environement
If you need special environment setting, define them here.

> `VARIABLE: value`

## Rules
Each rule consists of up to eight parts:

### `if`
Formula describing a logical condition

> `if: sunrise < 7:00` # test if sun rises before 7:00

*Optional, defaults to `true`*

### `then`, `else`
Formulas or times to use if the condition is true/false

> `then: sunrise + 3:00` # three hours after sunrise

You can leave then empty to ignore them.

*Optional, defaults to `null`; if omitted the rule will be skipped*

### `day`, `month`, `dow`
The 3rd to 5th field (day, month and day of week) of a crontab entry.

See `man 5 crontab` or eg. [Wikipedia](https://en.wikipedia.org/wiki/Cron#CRON_expression)
for syntax help.

*Optional, defaults to `*`*

### `user`
The user which will run the command

*Optional, defaults to `root`*

### `cronic`
If you have installed [cronic](https://habilis.net/cronic/),
you can activate its usage with this flag.

*Optional, defaults to false*

### `nice`
Run "cmd" with an adjusted niceness, which affects process scheduling.
Niceness values range from -20 (most favorable to the process)
to 19 (least favorable to the process).

*Optional, defaults to false*

### `cmd`
The command to run via cron.

*Required, missing command will raise an exception*

## Output
The resulting cron data will be written by default to a file named like this:

> Configuration file: /path/to/**default**.yaml\
> Cron file: /etc/cron.d/**suncron-php-default**

## Run

If you defined your configuration, test it with

    $ suncron-php -t <your-config-file.yaml>

The configuration file can be given as absolute or relative path.

#### Example

    $ suncron-php -t dist/daylight.yaml

    SunCron/PHP v1.1.0 (2017-11-11)

    TEST mode, don't write crontab
    Config file : /home/user/suncron-php/dist/daylight.yaml
    Sunrise - Sunset : 7:32 - 15:37
    ------------------------------------------------------------
    1 | sunrise |  | * | * | *
    32 7 * * * root echo '7:32-15:37' >/run/daylight
    ------------------------------------------------------------
    1 | sunset |  | * | * | *
    37 15 * * * root rm /run/daylight 2>/dev/null
    ------------------------------------------------------------
    1 | sunrise - 1:00 |  | * | * | *
    32 6 * * * root printf "1510385520\n1510414620\n" >/run/daylight-60
    ------------------------------------------------------------
    1 | sunset + 1:00 |  | * | * | *
    37 16 * * * root rm /run/daylight-60 2>/dev/null
    ------------------------------------------------------------
    #
    # WARNING - this file is automatic generated, changes will be lost!
    #

    # Run itself and recreate file each night
    5 0 * * * root /usr/bin/suncron-php /home/user/suncron-php/dist/daylight.yaml

    # Suncron entries
    32 7 * * * root echo '7:32-15:37' >/run/daylight
    37 15 * * * root rm /run/daylight 2>/dev/null
    32 6 * * * root printf "1510385520\n1510414620\n" >/run/daylight-60
    37 16 * * * root rm /run/daylight-60 2>/dev/null

If you are not fine with the outcome, e.g. if you find "invalid" times,
run the verbose test which will show the calculations made.

#### Example

    $ suncron-php -t -v dist/daylight.yaml

    SunCron/PHP v1.1.0 (2017-11-11)

    TEST mode, don't write crontab
    Config file : /home/user/suncron-php/dist/daylight.yaml
    Sunrise - Sunset : 7:32 - 15:37
    ------------------------------------------------------------
    1 | sunrise |  | * | * | *
    IF                  : 1
    IF result           : true
    THEN parsed         : 7:32
    THEN parsed         : 27120 (in seconds)
    THEN result         : 07:32
    32 7 * * * root echo '7:32-15:37' >/run/daylight
    ------------------------------------------------------------
    1 | sunset |  | * | * | *
    IF                  : 1
    IF result           : true
    THEN parsed         : 15:37
    THEN parsed         : 56220 (in seconds)
    THEN result         : 15:37
    37 15 * * * root rm /run/daylight 2>/dev/null
    ------------------------------------------------------------
    1 | sunrise - 1:00 |  | * | * | *
    IF                  : 1
    IF result           : true
    THEN parsed         : 7:32 - 1:00
    THEN parsed         : 27120 - 3600 (in seconds)
    THEN result         : 06:32
    32 6 * * * root printf "1510385520\n1510414620\n" >/run/daylight-60
    ------------------------------------------------------------
    1 | sunset + 1:00 |  | * | * | *
    IF                  : 1
    IF result           : true
    THEN parsed         : 15:37 + 1:00
    THEN parsed         : 56220 + 3600 (in seconds)
    THEN result         : 16:37
    37 16 * * * root rm /run/daylight-60 2>/dev/null
    ------------------------------------------------------------
    #
    # WARNING - this file is automatic generated, changes will be lost!
    #

    # Run itself and recreate file each night
    5 0 * * * root /usr/bin/suncron-php /home/user/suncron-php/dist/daylight.yaml

    # Suncron entries
    32 7 * * * root echo '7:32-15:37' >/run/daylight
    37 15 * * * root rm /run/daylight 2>/dev/null
    32 6 * * * root printf "1510385520\n1510414620\n" >/run/daylight-60
    37 16 * * * root rm /run/daylight-60 2>/dev/null

If you are then fine with the result, run your setup **once** to create the
final cron file. **Always** run SunCron/PHP for this with `root` privileges,
must write files in `/etc/cron.d/`!

    $ sudo suncron-php dist/daylight.yaml

This will create the cron file. You **don't need** to run this again,
the cron file contains a call to recreate the cron file automatic each night.

To remove the cron file, run also as `root`

    $ sudo suncron-php -r dist/daylight.yaml
