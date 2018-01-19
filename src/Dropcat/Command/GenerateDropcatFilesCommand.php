<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class GenerateDropcatFilesCommand extends DropcatCommand
{
    protected static $defaultName = 'generate:dropcat-files';
    protected function configure()
    {
        $HelpText = '<info>generate dropcat yml-files</info>';

        $this->setName("generate:dropcat-files")
            ->setDescription("generates dropcat files")
            ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $type = ['Drupal', 'WordPress', 'LAMP', 'Static', 'Symfony'];
        $systemType = new Question("Please enter the type, valid opitions: Drupal (default), WordPress, LAMP, Static, Symfony:\n", 'Drupal');
        $systemType->setAutocompleterValues($type);
        $systemTypeChoice = $helper->ask($input, $output, $systemType);

        $output->writeln('You have just selected: <info>'.$systemTypeChoice . '</info>');
    }
}
