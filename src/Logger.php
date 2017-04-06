<?php
/**
 *
 */
namespace Suncron;

/**
 *
 */
class Logger
{
    /**
     *
     */
    public function __construct(\Aura\Cli\Stdio $stdio, $level=0)
    {
        $this->stdio = $stdio;
        $this->level = $level;
    }

    /**
     *
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     *
     */
    public function out($level=0)
    {
        if ($level > $this->level) return;

        $args = func_get_args();
        // Shift out level
        array_shift($args);

        $label = array_shift($args);

        $this->stdio->out(
            empty($args)
          ? $label
          : sprintf('<<bold>>%-20s<<reset>>: %s', $label, implode(' ', $args))
        );

        // Always reset format
        $this->stdio->outln('<<reset>>');
    }

    /**
     *
     */
    public function err($level=0)
    {
        if ($level > $this->level) return;

        $args = func_get_args();
        // Shift out level
        array_shift($args);

        $label = array_shift($args);

        $this->stdio->err(
            empty($args)
          ? $label
          : sprintf('<<bold>>%-20s<<reset>>: %s', $label, implode(' ', $args))
        );

        // Always reset format
        $this->stdio->errln('<<reset>>');
    }

    /**
     *
     */
    public function error($msg)
    {
    	$this->stdio->errln('<<redbg>>ERROR: '.$msg.'<<reset>>');
    }

}
