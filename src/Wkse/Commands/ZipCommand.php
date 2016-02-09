<?php

namespace Wkse\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ZipCommand extends Command {

    protected function configure()
    {
      $dir = explode('/', getcwd());
      $home_dir=$dir[count($dir)-1];

        $folder = $home_dir;
        $this->setName("wkse:zip")
             ->setDescription("Zip deploy folder")
             ->setDefinition( array (
               new InputOption('folder', 'f', InputOption::VALUE_OPTIONAL, 'Folder to zip', $folder),
             ))
             ->setHelp('Zip');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $folder = $input->getOption('folder');
        $process = new Process("zip -r $folder.zip .");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
        $output->writeln($folder);
    }
}

?>
