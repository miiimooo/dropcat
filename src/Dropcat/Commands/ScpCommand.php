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

class ScpCommand extends Command {

    protected function configure()
    {
      $folder = 'foo.zip';
      $server = 'localhost';
      $user = 'ubuntu';
      $targetdir = '/tmp';
      $port = '22';
      $timeout = '3600';

      $this->setName("dropcat:scp")
           ->setDescription("Upload archived folder or file via scp")
           ->setDefinition( array (
             new InputOption('folder', 'f', InputOption::VALUE_OPTIONAL, 'Folder', $folder),
             new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
             new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
             new InputOption('targetdir', 't', InputOption::VALUE_OPTIONAL, 'Targetdir', $targetdir),
             new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
             new InputOption('timeout', 'o', InputOption::VALUE_OPTIONAL, 'Timeout', $timeout),
           ))
           ->setHelp('Scp');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $folder = $input->getOption('folder');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $targetdir = $input->getOption('targetdir');
        $port = $input->getOption('port');
        $timeout = $input->getOption('timeout');

        $process = new Process("scp -C  -P $port $folder $user@$server:$targetdir");
        $process->setTimeout($timeout);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: wkse:scp finished</info>');
    }
}

?>
