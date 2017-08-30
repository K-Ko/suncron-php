#!/usr/bin/env php
<?php
/**
 *
 */
#ini_set('display_errors', 1);
#error_reporting(-1);

require_once __DIR__.'/vendor/autoload.php';

// Aura CLI classes
use Aura\Cli\CliFactory;
use Aura\Cli\Context\OptionFactory;
use Aura\Cli\Help;
use Aura\Cli\Status;

// Own classes
use Suncron\Logger;
use Suncron\Dipper;
use Suncron\TimesParser;

$cli     = new CliFactory;
$context = $cli->newContext($GLOBALS);
$logger  = new Logger($cli->newStdio());

$version = PHP_EOL
         . '<<bold yellow>>* * *  SunCron/PHP v'.file_get_contents(__DIR__.'/.version').'  * * *<<reset>>'
         . PHP_EOL;

/**
 * Command line options
 */
$options = [
    's,stdout'  => 'Write cron entries to stdout.',
    'o,output:' => 'Write cron entries to file <value>.',
    't,test'    => 'Test mode, only analyse configuration (sets verbosity to 1)',
    'v*'        => 'Verbosity level, multiple use increases verbosity (max. 2)',
    'help'      => 'This help',
];

$getopt = $context->getopt(array_keys($options));

$error = false;

if ($getopt->hasErrors()) {
    $error = $getopt->getErrors();
    foreach ($error as $err) {
        $logger->error($err->getMessage());
    }
    $logger->err();
};

$showHelp = $getopt->get('--help');

if (!$showHelp && '' == $conf_file = $getopt->get(1)) {
    $error = true;
    $logger->error('Missing configuration file!');
    $logger->err();
}

/**
 * Usage
 */
if ($error || $showHelp) {
    $logger->err(0, $version);

    $help = new Help(new OptionFactory);

    $help->setSummary('Create crontabs relative to sunrise and sunset.');
    $help->setUsage('[options] <config file>');
    $help->setOptions($options);
    $help->setDescr(
    	'See <<bold>>dist/*.yaml<<reset>> for examples and details'.PHP_EOL
       .'    Github: https://github.com/K-Ko/suncron-php'
    );
    $logger->err(0, $help->getHelp($getopt->get(0)));

    exit(isset($errors) ? Status::FAILURE : Status::SUCCESS);
}

$test      = $getopt->get('-t', false);
$verbose   = count($getopt->get('-v', []));
$conf_file = realpath($conf_file);

if ($test) {
    $logger->err(0, $version);
    $logger->err(0, '<<bold>><<green>>TEST mode, don\'t write crontab'.PHP_EOL);
    $verbose++;
}

$logger->setLevel($verbose);

/**
 *
 */
try {

    $logger->err(1, 'Config file', $conf_file);

    $conf = array_merge(
        [ 'Location' => [], 'Environment' => [], 'Rules' => [] ],
        Dipper::parseFile($conf_file)
    );

    $loc = array_merge(
    	[ 'Timezone' => file_get_contents('/etc/timezone'), 'Zenith' => 90+50/60 ],
    	$conf['Location']
    );

    $TimesParser = new TimesParser(
        $loc['Latitude'], $loc['Longitude'], $loc['Timezone'], $loc['Zenith']
    );

    $logger->err(1, 'Sunrise - Sunset', $TimesParser->getSunrise(), '-', $TimesParser->getSunset());

    $crons = [];

    // Analyse rules
    foreach ($conf['Rules'] as $idx=>$rule) {

        $rule = array_merge(
            [ 'if' => true, 'then' => null, 'else' => null,
              'day' => '*', 'month' => '*', 'dow' => '*',
              'user' => 'root', 'cmd' => null ],
            $rule
        );

        if (empty($rule['cmd'])) {
            throw new Exception('Missing command to run in rule #'.($idx+1));
        }

        $logger->err(1, str_repeat('-', 60));
        $logger->err(1, implode(' | ', $rule));
        $logger->err(2, 'IF', $rule['if']);

        $res = $TimesParser->parse($rule['if']);
        foreach ($TimesParser->getDebug() as $d) {
        	$logger->err(2, 'IF parsed', $d);
        }
        $logger->err(2, 'IF result', ['false','true'][$res]);

        if (
            ( $res && $rule['then'] && $time = $TimesParser->parse($rule['then'])) ||
            (!$res && $rule['else'] && $time = $TimesParser->parse($rule['else']))
        ) {
            $label = ['ELSE','THEN'][$res];

            foreach ($TimesParser->getDebug() as $d) {
            	$logger->err(2, $label.' parsed', $d);
            }
            $logger->err(2, $label.' result', date('H:i', $time));

            $cmd = sprintf("%02d\t%02d\t%s\t%s\t%s\t%s\t%s",
                date('i', $time), date('H', $time),
                $rule['day'], $rule['month'], $rule['dow'], $rule['user'],
                str_replace(
                    [ '$sunrise_ts', '$sunset_ts', '$sunrise', '$sunset' ],
                    [ $TimesParser->getSunriseInt(), $TimesParser->getSunsetInt(),
                      $TimesParser->getSunrise(), $TimesParser->getSunset() ],
                    $rule['cmd']
                )
            );
            $logger->err(1, str_replace("\t", ' ', $cmd));
            $crons[] = $cmd;
        }
    }

    if (count($crons)) {

        $logger->err(1, str_repeat('-', 60));

        if ($test || $getopt->get('-s')) {
            // Output crontab to stdout
            $output = '-';
        } else {
            $output = $getopt->get('-o');
            if ('' == $output) {
                // Build file name from config file
                $output = pathinfo($conf_file);
                $output = '/etc/cron.d/suncron-php-'.str_replace('.', '_', $output['filename']);
            }
        }

        $build = realpath($getopt->get(0)).' '.$conf_file;

        $crontab = [
            '#',
            '# WARNING - this file is automatically generated, changes will be lost!',
            '#',
            '# Build with',
            '# ' . $build,
            '#',
            null
        ];

        if (!empty($conf['Environment'])) {
            // Add  settings to cron file
            foreach ($conf['Environment'] as $key=>$value) {
            	$crontab[] = sprintf('%s = "%s"', $key, $value);
            }
            $crontab[] = null;
        }

        // Add header lines and environment to cron lines
        $crontab = implode(PHP_EOL, array_merge($crontab, $crons));

        if ($output == '-') {
            $logger->out(0, '<<blue>>'.$crontab);
        } elseif ($bytes = @file_put_contents($output, $crontab)) {
            $logger->err(2, '<<blue>>'.$crontab);
            $logger->err(1, '<<green>>Wrote '.$bytes.' Bytes to \''.$output.'\'');
            $logger->err(1, str_repeat('-', 60));
            $logger->err(1, 'Put this into root crontab:');
            $logger->err(1, '0 * * * * ' . $build);
        } else {
            throw new Exception('Can\'t write \''.$output.'\', must run as root');
        }
    }

} catch (Exception $e) {
    $logger->err();
    $logger->error($e->getMessage());
    exit(Status::FAILURE);
}

exit(Status::SUCCESS);
