<?php
namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TarCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>tar</info> command will create a gzipped tar.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat tar</info>
To override config in dropcat.yml, using options:
<info>dropcat tar -f foofolder -t ./ -se __ -a mysitename -bi 42</info>';

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
                      'bi',
                      InputOption::VALUE_OPTIONAL,
                      'Id',
                      $this->configuration->localEnvironmentBuildId()
                  ),
                  new InputOption(
                      'temp-path',
                      't',
                      InputOption::VALUE_OPTIONAL,
                      'Temp (./ for current dir)',
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
                      'separator',
                      'se',
                      InputOption::VALUE_OPTIONAL,
                      'Name separator',
                      $this->configuration->localEnvironmentSeparator()
                  ),
              )
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ignore_files     = $this->getFilesToIgnore();
        $path_to_app      = $input->getOption('folder');
        $build_id         = $input->getOption('build-id');
        $temp_path        = $input->getOption('temp-path');
        $app_name         = $input->getOption('app-name');
        $separator        = $input->getOption('separator');

        $path_to_tar_file = $temp_path . DIRECTORY_SEPARATOR . $app_name . $separator . $build_id . '.tar';
        $basepath_for_tar = $path_to_app;

        if (!(`which tar`)) {
          throw new \RuntimeException('tar command doesn\'t exist');
        }

        $excludes_file_name = $path_to_tar_file . '.excludes';
        $excludes_file =  fopen($excludes_file_name, "w");
        if (isset($ignore_files)) {
          foreach ($ignore_files as $ignore_file) {
            fwrite($excludes_file, $ignore_file . "\n");
          }
        }
        fclose($excludes_file);

        if ($output->isVerbose()) {
            echo "Build number from CI server is: " . getenv('BUILD_NUMBER') . "\n";
            echo "Build date from CI server is: " . getenv('BUILD_DATE') . "\n";
        }

        $tar = new Process('tar -cf ' . $path_to_tar_file . ' -X ' . $excludes_file_name . ' ' . $basepath_for_tar);
        $tar->setTimeout(3600);
        $tar->run();

        if (!$tar->isSuccessful()) {
          throw new ProcessFailedException($tar);
        }

        unlink($excludes_file_name);
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
        return $this->configuration->deployIgnoreFiles();
    }
}
