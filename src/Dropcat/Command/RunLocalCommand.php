<?php

namespace Dropcat\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RunLocalCommand extends RunCommand
{

    protected function configure()
    {
        $HelpText = 'The <info>run-local</info> command will run script or command.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat run-local</info>
To override config in dropcat.yml, using options:
<info>dropcat run-local --input=script.sh</info>';

        $this->setName("run-local")
            ->setDescription("run command or script on local environment")
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
        $process = new Process("$input");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output->writeln('<info>Task: run:local finished</info>');
    }
}
