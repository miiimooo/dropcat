<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;
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
        $HelpText = 'The <info>run:npm-install</info> command will run npm install.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat run:npm-install</info>
To override config in dropcat.yml, using options:
<info>dropcat run-local --input=script.sh</info>';

        $this->setName("run:npm-install")
            ->setDescription("run npm install")
            ->setDefinition(
                array(
                    new InputOption(
                        'input',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        'Input',
                        $this->configuration->localEnvironmentRun()
                    ),

                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input = $input->getOption('input');
        $packageJson = file_get_contents('package.json');
        $json = json_decode($packageJson);
        if (isset($json->{'nodeVersion'})) {
            $nodeVersion = $json->{'nodeVersion'};
            exec("nvm install $nodeVersion");
        }
        $output->writeln('<info>Task: run:npm-install finished</info>');
    }
}
