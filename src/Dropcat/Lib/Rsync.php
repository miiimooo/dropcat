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
class Rsync
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

    public function multi($original, $clone, $verbose)
    {

        $id_file = $original['web']['id-file'];
        $user = $original['web']['user'];
        $host = $original['web']['host'];

        $from = $original['web']['alias-path'] . '/web/sites/' .
          $original['web']['site-domain'] . '/files/';

        $to = $clone['web']['alias-path'] . '/web/sites/' .
          $clone['web']['site-domain'] . '/files';


        $this->output->writeln("<info>$this->mark starting rsyncing files</info>");

        $rsync = new Process(
            "ssh -i $id_file $user@$host rsync -avz $from $to"
        );
        $rsync->setTimeout(999);
        $rsync->run();

        // executes after the command finishes
        if (!$rsync->isSuccessful()) {
            throw new ProcessFailedException($rsync);
        }
        echo $rsync->getOutput();

        $this->output->writeln("<info>$this->mark finnished rsyncing files</info>");
    }
}
