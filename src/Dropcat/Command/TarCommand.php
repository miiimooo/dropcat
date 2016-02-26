<?php

namespace Dropcat\Command;

use Archive_Tar;
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

class TarCommand extends Command
{
    /** @var Configuration configuration */
    private $configuration;

    protected function configure()
    {

        $this->configuration = new Configuration();

        $this->setName("dropcat:tar")
          ->setDescription("Tar folder")
          ->setDefinition(array(
            new InputOption('folder', 'f', InputOption::VALUE_OPTIONAL,
              'Folder to tar', $this->configuration->localEnvironmentAppPath()),
          ))
          ->setHelp('Tar');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ignore_files     = $this->getFilesToIgnore();


        $path_to_app      = $input->getOption('folder');
        $path_to_tar_file = $this->configuration->pathToTarFileInTemp();

        $basepath_for_tar = $path_to_app;

        try {
            $tar = new Archive_Tar($path_to_tar_file);
            $tar->setIgnoreList($ignore_files);
            $tar->createModify($path_to_app, '', $basepath_for_tar);
        } catch (\Exception $e) {
            var_dump($e);
        }

        /*
         * $process = new Process("tar $ignore_files -cvf $path_to_tar_file -C $path_to_app .");
        $process->run();
          // executes after the command finishes
          if (!$process->isSuccessful()) {
              throw new ProcessFailedException($process);
          }
          echo $process->getOutput();
        */
        $output->writeln('<info>Task: dropcat:tar finished</info>');
    }

    protected function getFilesToIgnore()
    {
        $filesToIgnore = \explode(' ',$this->configuration->deployIgnoreFilesTarString());
        foreach($filesToIgnore as &$file) {
            $file = str_replace("--exclude='",'', $file);
            $file = substr($file,0,-1);
        }
        return $filesToIgnore;
    }

    protected function getTarFileLocation()
    {
        return $this->configuration->pathToTarFileInTemp();
    }

}

?>
