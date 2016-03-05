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
        $this->setName("tar")
          ->setDescription("Tar folder")
          ->setDefinition(
              array(
                  new InputOption(
                      'folder',
                      'f',
                      InputOption::VALUE_OPTIONAL,
                      'Folder',
                      $this->configuration->localEnvironmentAppPath()
                  ),
                  new InputOption(
                      'build-id',
                      'i',
                      InputOption::VALUE_OPTIONAL,
                      'Id',
                      $this->configuration->localEnvironmentBuildId()
                  ),
                  new InputOption(
                      'temp-path',
                      't',
                      InputOption::VALUE_OPTIONAL,
                      'Temp',
                      $this->configuration->localEnvironmentTmpPath()
                  ),
                  new InputOption(
                      'app-name',
                      'a',
                      InputOption::VALUE_OPTIONAL,
                      'App name',
                      $this->configuration->localEnvironmentAppName()
                  ),
                  new InputOption(
                      'seperator',
                      's',
                      InputOption::VALUE_OPTIONAL,
                      'Name seperator',
                      $this->configuration->localEnvironmentSeperator()
                  ),
              )
          )
          ->setHelp('Tar');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ignore_files     = $this->getFilesToIgnore();
        $path_to_app      = $input->getOption('folder');
        $build_id         = $input->getOption('build-id');
        $temp_path        = $input->getOption('temp-path');
        $app_name         = $input->getOption('app-name');
        $seperator        = $input->getOption('seperator');

        $path_to_tar_file = $temp_path . $app_name . $seperator . $build_id . '.tar';
        $basepath_for_tar = $path_to_app;


        $tar = new Archive_Tar($path_to_tar_file, true);
        $tar->setIgnoreList($ignore_files);
        $success = $tar->createModify($path_to_app, '', $basepath_for_tar);
        if (!$success) {
            /** @var \PEAR_Error $error_object */
            $error_object = $tar->error_object;
            $exceptionMessage = sprintf(
                "Unable to tar folder, Error message:\n%s\n\n",
                $error_object->message
            );
            throw new \RuntimeException($exceptionMessage, $error_object->code);
        }
        $output->writeln('<info>Task: tar finished</info>');

    }

    /**
     * We convert the usual tar --exclude='...' list to an array with only the
     * the name of the file/path to ignore.
     *
     * @return array
     */
    protected function getFilesToIgnore()
    {
        $filesToIgnore = \explode(' ', $this->configuration->deployIgnoreFilesTarString());
        foreach ($filesToIgnore as &$file) {
            $file = substr($file, 11, -1);
        }
        return $filesToIgnore;
    }
}
