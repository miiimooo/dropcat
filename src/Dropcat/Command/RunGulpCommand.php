<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Dropcat\Command\RunCommand;

class RunGulpCommand extends RunCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>node:gulp</info> command will run npm install.
    <comment>Samples:</comment>
    To run with default options (using config from dropcat.yml in the currrent dir):
    <info>dropcat node:gulp</info>
    To override config in dropcat.yml, using options:
    <info>dropcat node:gulp --gulp-dir=/foo/bar --nvmrc=/foo/bar/.nvmrc</info>';

        $this->setName("node:gulp")
          ->setDescription("run gulp")
          ->setDefinition(
              array(
                new InputOption(
                    'gulp-dir',
                    'gd',
                    InputOption::VALUE_REQUIRED,
                    'Directory with gulpfile',
                    $this->configuration->gulpDirectory()
                ),
                new InputOption(
                    'nvm-dir',
                    'nd',
                    InputOption::VALUE_REQUIRED,
                    'NVM directory',
                    $this->configuration->nodeNvmDirectory()
                ),
                new InputOption(
                    'nvmrc',
                    'nc',
                    InputOption::VALUE_OPTIONAL,
                    'Path to .nvmrc file',
                    $this->configuration->nodeNvmRcFile()
                ),
                new InputOption(
                    'gulp-options',
                    'go',
                    InputOption::VALUE_OPTIONAL,
                    'Gulp options',
                    $this->configuration->gulpOptions()
                ),
                new InputOption(
                    'node-env',
                    'ne',
                    InputOption::VALUE_OPTIONAL,
                    'Node environment',
                    $this->configuration->nodeEnvironment()
                ),
              )
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nvmDir = $input->getOption('nvm-dir');
        $gulpDir = $input->getOption('gulp-dir');
        $gulpOptions = $input->getOption('gulp-options');
        $nodeEnv = $input->getOption('node-env');
        $nodeNvmRcFile = $input->getOption('nvmrc');

        if ($gulpDir === null) {
            $gulpDir = '.';
        }
        if ($nodeNvmRcFile === null) {
            $nodeNvmRcFile = getcwd() . '/.nvmrc';
        }

        if (!$this->nvmrcFileExists($nodeNvmRcFile)) {
            throw new Exception('No .nvmrc file found.');
        }

        $env = null;
        if (isset($nodeEnv)) {
            $env = 'NODE_ENV=' . $nodeEnv;
        }
        $output->writeln('<info>Installing gulp stuff</info>');
        $gulp = $this->runProcess("bash -c 'source $nvmDir/nvm.sh' && . $nvmDir/nvm.sh && nvm use && cd $gulpDir && $env gulp $gulpOptions");
        $gulp->setTimeout(3600);
        $gulp->run();
        echo $gulp->getOutput();
        if (!$gulp->isSuccessful()) {
            throw new ProcessFailedException($gulp);
        }
        $output->writeln('<info>Task: node:gulp finished</info>');
    }

    /**
     * @param $nodeNvmRcFile
     * @codeCoverageIgnore
     * @return bool
     */
    protected function nvmrcFileExists($nodeNvmRcFile)
    {
        return file_exists($nodeNvmRcFile);
    }
}
