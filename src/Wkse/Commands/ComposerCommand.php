<?php

namespace Wkse\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ComposerCommand extends Command {

    protected function configure()
    {
        $task = 'update';
        $this->setName("wkse:composer")
             ->setDescription("Run composer task")
             ->setDefinition( array (
               new InputOption('task', 't', InputOption::VALUE_OPTIONAL, 'Composer task', $task),
             ))
             ->setHelp('Composer');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $task = $input->getOption('task');
        $output->writeln($task);
    }
}

?>
