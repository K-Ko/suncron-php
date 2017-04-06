<?php
/**
 *
 */
namespace Suncron;

/**
 *
 */
class TimesParser
{
    /**
     *
     */
    public function __construct($latitude, $longitude, $timezone, $zenith)
    {
        // Set time zone
        $timezone = trim($timezone);

        if (!@date_default_timezone_set($timezone)) {
            throw new \Exception('Timezone ID \''.$timezone.'\' is invalid!');
        }

        // Calculate today's sunrise and sunset
        $now = time();
        $gmt_offset = date('Z', $now) / 3600;

        $this->sunrise = date_sunrise(
            $now, SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt_offset
        );
        if (substr($this->sunrise, 0, 1) == '0') {
            $this->sunrise = substr($this->sunrise, 1);
        }

        $this->sunset = date_sunset(
            $now, SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $gmt_offset
        );
        if (substr($this->sunset, 0, 1) == '0') {
            $this->sunset = substr($this->sunset, 1);
        }

        $this->debug = [];
    }

    /**
     *
     */
    public function getSunrise()
    {
        return $this->sunrise;
    }

    /**
     *
     */
    public function getSunriseInt()
    {
        return strtotime($this->sunrise);
    }

    /**
     *
     */
    public function getSunset()
    {
        return $this->sunset;
    }

    /**
     *
     */
    public function getSunsetInt()
    {
        return strtotime($this->sunset);
    }

    /**
     *
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     *
     */
    public function parse($instr)
    {
        $this->debug = [];

        // Make sure any further calculations have NO daylight saving time offset
        date_default_timezone_set('UTC');

        $str = str_replace(
            ['sunrise', 'sunset'],
            [$this->sunrise, $this->sunset],
            strtolower($instr)
        );

        // Any replacements?
        if ($str <> $instr) $this->debug[] = [1, $str];

        if (preg_match_all('~[0-9]{1,2}:[0-9]{2}~', $str, $times)) {
            foreach ($times[0] as $time) {
                $timestamp = strtotime($time);
                $midnight  = floor($timestamp / 86400) * 86400;
                $str = str_replace($time, $timestamp - $midnight, $str);
            }
            $this->debug[] = [3, $str . ' (in seconds)'];
        }

        // http://stackoverflow.com/a/20025298
        $res = @eval('return '.$str.';');
        if ($err = error_get_last()){
            throw new \Exception($err['message'] . ' in \'' . $str . '\'!');
        }

        return $res;
    }

    // -----------------------------------------------------------------------
    // PROTECTED
    // -----------------------------------------------------------------------

    /**
     *
     */
    protected $sunrise;

    /**
     *
     */
    protected $sunset;

    /**
     *
     */
    protected $debug;

}
