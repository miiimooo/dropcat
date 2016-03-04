<?php

namespace Dropcat\Commands;

use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class TarCommand extends Command {

  protected function configure()
    {
      $this->configuration = new Configuration();
      $this->setName("tar")
        ->setDescription("Tar folder")
        ->setDefinition( array (
          new InputOption('folder', 'f', InputOption::VALUE_OPTIONAL, 'Folder to tar', $this->configuration->localEnvironmentAppPath()),
        ))
        ->setHelp('Tar');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $ignore_files = $this->configuration->deployIgnoreFilesTarString();
        $path_to_app = $input->getOption('folder');
        $path_to_tar_file = $this->configuration->pathToTarFileInTemp();

        $process = new Process("tar $ignore_files -cvf $path_to_tar_file -C $path_to_app .");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
        $output->writeln('<info>Task: tar finished</info>');
    }

}

?>
