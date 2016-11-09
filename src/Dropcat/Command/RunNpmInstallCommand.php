<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Dropcat\Command\RunCommand;

class RunNpmInstallCommand extends RunCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>node:npm-install</info> command will run npm install.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat node:npm-install</info>
To override config in dropcat.yml, using options:
<info>dropcat run-local --package-json=/foo/bar/package.json</info>';

        $this->setName("node:npm-install")
            ->setDescription("do a npm install")
            ->setDefinition(
                array(
                    new InputOption(
                        'nvm-dir',
                        'nd',
                        InputOption::VALUE_REQUIRED,
                        'NVM directory',
                        $this->configuration->nodeNvmDirectory()
                    ),
                    new InputOption(
                        'package-json',
                        'pj',
                        InputOption::VALUE_OPTIONAL,
                        'Path to package.json',
                        $this->configuration->nodePackageJsonFile()
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nvmDir = $input->getOption('nvm-dir');
        $packageJsonFile = $input->getOption('package-json');
        if ($packageJsonFile === null) {
            $packageJsonFile = 'package.json';
        }
        if (file_exists($packageJsonFile)) {
            $packageJson = file_get_contents($packageJsonFile);
            $decodeJson = json_decode($packageJson);
            if (isset($decodeJson->{'nodeVersion'})) {
                $nodeVersion = $decodeJson->{'nodeVersion'};
                $output->writeln('<info>Installing/setting node version ' . $nodeVersion . '</info>');
                $npmInstall = new Process("source $nvmDir/nvm.sh && . $nvmDir/nvm.sh && nvm install $nodeVersion && npm install");
                $npmInstall->setTimeout(3600);
                $npmInstall->run();
                echo $npmInstall->getOutput();
                if (!$npmInstall->isSuccessful()) {
                    throw new ProcessFailedException($npmInstall);
                }
            }
        } else {
            $output->writeln('<info>No package.json file found, please check your configuration.</info>');
            exit(1);
        }
        $output->writeln('<info>Task: node:npm-install finished</info>');
    }
}
