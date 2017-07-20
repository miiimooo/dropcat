<?php
namespace Dropcat\Lib;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Install
 *
 * Checking if it is Drupal, and which version.
 *
 * @package Dropcat\Lib
 */
class Install
{

    public $mark;
    public $output;

    public function __construct()
    {
        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $this->mark = $style->colorize('yellow', $mark);
        $this->output = new ConsoleOutput();
    }

    public function drupal($config, $verbose)
    {
        $profile = $config['profile'];
        $site_name = $config['site-name'];
        $subdir = $config['subdir'];
        $alias = $config['drush-alias'];
        $v = '';
        if ($verbose == true) {
            $v = ' -v';
        }
        $this->output->writeln("<info>$this->mark starting installation of $alias</info>");
        $install= new Process(
          "drush @$alias si $profile --account-name=admin --account-pass=admin --site-name=$site_name --sites-subdir=$subdir --yes $v"
        );
        $install->setTimeout(999);
        $install->run();
        // executes after the command finishes
        if (!$install->isSuccessful()) {
            throw new ProcessFailedException($install);
        }
        echo $install->getOutput();
    }
}
