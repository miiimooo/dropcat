<?php

namespace Dropcat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class SymlinkCommand extends Command {

    protected function configure() {
      $original = '/my/path';
      $target = '/my/target';
      $server = 'localhost';
      $user = 'ubuntu';
      $local = true;
      $port = '22';
      $timeout = '120';

      $this->setName("dropcat:symlink")
        ->setDescription("Create symlink")
        ->setDefinition( array (
          new InputOption('original', 'o', InputOption::VALUE_OPTIONAL, 'Original', $original),
          new InputOption('target', 't', InputOption::VALUE_OPTIONAL, 'Target', $target),
          new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
          new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
          new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
          new InputOption('timeout', 'to', InputOption::VALUE_OPTIONAL, 'Timeout', $timeout),
          new InputOption('local', 'l', InputOption::VALUE_OPTIONAL, 'Local', $local),
        ))
        ->setHelp('Create symlink');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
      $original = $input->getOption('original');
      $target = $input->getOption('target');
      $server = $input->getOption('server');
      $user = $input->getOption('user');
      $port = $input->getOption('port');
      $timeout = $input->getOption('timeout');
      $local = $input->getOption('local');
      if ($local == false) {
        $process = new Process("ssh -p $port $user@$server << EOF
        rm $target 2> /dev/null
        ln -s $original $target
EOF");
      }
      else {
          $process = new Process("rm $target 2> /dev/null && ln -s $original $target");
      }
      $process->setTimeout($timeout);
      $process->run();
      if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
      }
      echo $process->getOutput();
    }
}

?>
