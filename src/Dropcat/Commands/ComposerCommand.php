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

class ComposerCommand extends Command {

    protected function configure()
    {

      $server = 'localhost';
      $user = 'ubuntu';
      $web_dir = '/tmp';
      $port = '22';
      $timeout = '3600';

      $this->setName("composer")
           ->setDescription("Upload archived folder or file via scp")
           ->setDefinition( array (
             new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
             new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
             new InputOption('web_dir', 'w', InputOption::VALUE_OPTIONAL, 'Web dir', $web_dir),
             new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
             new InputOption('timeout', 'o', InputOption::VALUE_OPTIONAL, 'Port', $timeout),
           ))
           ->setHelp('Composer');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $web_dir = $input->getOption('web_dir');
        $port = $input->getOption('port');
        $timeout = $input->getOption('timeout');

        $process = new Process("ssh -p $port $user@$server << EOF
        cd $web_dir
        rm -rf vendor
        composer update
        EOF");
        $process->setTimeout($timeout);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: composer finished</info>');
    }
}

?>
