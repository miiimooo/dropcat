<?php
namespace Dropcat\Lib;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class ConfigSplit
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
        $install= new Process(
            "drush @$alias csex --yes $v"
        );
        $install->setTimeout(999);
        $install->run();
        // executes after the command finishes
        if (!$install->isSuccessful()) {
            throw new ProcessFailedException($install);
        }
        if ($this->verbose == true) {
            echo $install->getOutput();
        }
    }
}
