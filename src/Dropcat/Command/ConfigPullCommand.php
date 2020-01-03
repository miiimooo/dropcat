<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ConfigPullCommand extends DropcatCommand {

    protected function configure() {
        $HelpText = 'The <info>%command.name%</info> exports and transfers config from one environment to another.
<comment>For example, to transfer config from prod to the local site into the client-config folder:</comment>
<info>dropcat config-pull -s @prod -d @self -l client-config</info>';

        $this->setName("config-pull")
          ->setDescription("Configuration import")
          ->setDefinition(
            [
              new InputOption(
                'source',
                's',
                InputOption::VALUE_REQUIRED,
                'Source (drush alias)'
              ),
              new InputOption(
                'destination',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Destination (drush alias or default @self)',
                'self'
              ),
              new InputOption(
                'label',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Label (transfer to the \'label\' config directory of current site)'
              ),
              new InputOption(
                'timeout',
                'to',
                InputOption::VALUE_OPTIONAL,
                'Timeout',
                $this->configuration->timeOut()
              ),
            ]
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $source = $input->getOption('source');
        $dest = $input->getOption('destination');
        $label = $input->getOption('label');
        $timeout = $input->getOption('timeout');

        if ($output->isVerbose()) {
            echo 'Pulling config from: ' . $source . "\n";
            echo 'using config: ' . $dest . "\n";
        }

        $processCommand = "drush config:pull @$source @$dest -y";

        if (isset($label)) {
            $processCommand .= " --label $label";
        }

        if (!$output->isVerbose()) {
            $processCommand .= " -q";
        }

        $process = $this->runProcess($processCommand);

        $process->setTimeout($timeout);
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output->writeln('<info>Task: config:pull finished</info>');
    }
}
