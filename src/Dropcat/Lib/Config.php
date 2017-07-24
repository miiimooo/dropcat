<?php
namespace Dropcat\Lib;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Config
 *
 * Checking if it is Drupal, and which version.
 *
 * @package Dropcat\Lib
 */
class Config
{
    public $mark;
    public $output;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $this->mark = $style->colorize('yellow', $mark);
    }

    public function import($config, $verbose)
    {
        $alias = $config['drush-alias'];
        $v = ' -q';
        if ($verbose == true) {
            $v = ' -v';
        }
        $import= new Process(
            "drush @$alias cim --yes $v"
        );
        $import->setTimeout(999);
        $import->run();
        // executes after the command finishes
        if (!$import->isSuccessful()) {
            throw new ProcessFailedException($import);
        }
        echo $import->getOutput();
        $this->output->writeln("<info>$this->mark config imported for $alias</info>");
    }

}
