<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ResetLoginCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>reset-login</info> command resets admin login for a drupal site.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat reset-login</info>
To override config in dropcat.yml, using options:
<info>dropcat reset-login -d mysite</info>';

        $this->setName("reset-login")
            ->setDescription("Reset login")
            ->setDefinition(
                array(
                    new InputOption(
                        'drush_alias',
                        'd',
                        InputOption::VALUE_OPTIONAL,
                        'Drush alias',
                        $this->configuration->siteEnvironmentDrushAlias()
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_alias = $input->getOption('drush_alias');

        $output->writeln("<info>$this->start reset-login started</info>");

        if ($output->isVerbose()) {
            $output->writeln("<info>$this->mark using drush alias: $drush_alias</info>");
        }
        $process = new Process("drush @$drush_alias uli");
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
        $output->writeln("<info>$this->heart reset-login finished</info>");
    }
}
