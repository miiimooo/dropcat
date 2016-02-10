<?php

namespace Dropcat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class ComposerCommand extends Command {

    protected function configure()
    {
        $task = 'update';
        $this->setName("dropcat:composer")
             ->setDescription("Run composer task")
             ->setDefinition( array (
               new InputOption('task', 't', InputOption::VALUE_OPTIONAL, 'Composer task', $task),
             ))
             ->setHelp('Composer');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $task = $input->getOption('task');
        $process = new Process("composer $task");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: dropcat:composer finished</info>');
    }
}

?>
