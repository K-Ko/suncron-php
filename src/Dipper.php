<?php
/**
 *
 */
namespace Suncron;

/**
 * Extend with method to parse file
 */
class Dipper extends \secondparty\Dipper\Dipper
{
    /**
     * Takes a given file name and parses its content for YAML
     *
     * @param string  $file  The YAML file to parse
     * @return array
     * @throw \Exception
     */
    public static function parseFile($file)
    {
        if (!file_exists($file)) {
            throw new \Exception('Missing file \''.$file.'\'!', 1);
        }

        $php = static::parse(file_get_contents($file));

        if (empty($php)) {
            throw new \Exception('\''.$file.'\' is empty!', 2);
        }

        if (!is_array($php)) {
            throw new \Exception('Invalid file \''.$conf_file.'\'!', 3);
        }

        return $php;
    }

}
