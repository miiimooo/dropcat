<?php
namespace Dropcat\Lib;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class ConfigSplit
 *
 * Functions for handling drupal config splt.
 *
 * @package Dropcat\Lib
 */
class ConfigSplit
{

    public $verbose;

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function export($config)
    {
        $alias = $config['drush-alias'];
        $v = '';
        if ($this->verbose == true) {
            $v = ' -v';
        }
        $task= new Process(
            "drush @$alias csex --yes $v"
        );
        $task->setTimeout(999);
        $task->run();
        // executes after the command finishes
        if (!$task->isSuccessful()) {
            throw new ProcessFailedException($task);
        }
        if ($this->verbose == true) {
            echo $task->getOutput();
        }
    }
}
