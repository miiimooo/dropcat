<?php

namespace Dropcat\Command;

use Archive_Tar;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

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
        $path_to_tar_file = $this->getTarFileLocation();
        $basepath_for_tar = $path_to_app;

        $tar = new Archive_Tar($path_to_tar_file, true);
        $tar->setIgnoreList($ignore_files);
        $success = $tar->createModify($path_to_app, '', $basepath_for_tar);
        if ( !$success ) {
            /** @var \PEAR_Error $error_object */
            $error_object = $tar->error_object;
            $exceptionMessage = sprintf(
              "Unable to tar folder, Error message:\n%s\n\n",
              $error_object->message
            );
            throw new \RuntimeException($exceptionMessage, $error_object->code);
        }
        $output->writeln('<info>Task: dropcat:tar finished</info>');

    }

    /**
     * We convert the usual tar --exclude='...' list to an array with only the
     * the name of the file/path to ignore.
     *
     * @return array
     */
    protected function getFilesToIgnore()
    {
        $filesToIgnore = \explode(' ',
          $this->configuration->deployIgnoreFilesTarString());
        foreach ($filesToIgnore as &$file) {
            $file = substr($file, 11, -1);
        }
        return $filesToIgnore;
    }

    /**
     * Returns the path where the tar-file should be created and saved
     *
     * This makes it override:able, if we ever need that for a special project
     *
     * @return string
     */
    protected function getTarFileLocation()
    {
        return $this->configuration->pathToTarFileInTemp();
    }
}
?>
