<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateConfigCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> command will create a dropcat config file.';

        $this->setName("generate:config")
            ->setDescription("generates dropcat config.")
            ->setHelp($HelpText)
            ->setHidden(true)

            ->setDefinition(
                []
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);


        $io = new SymfonyStyle($input, $output);
        $io->title('Lorem Ipsum Dolor Sit Amet');
        $io->ask('Where are you from?', 'United States');
        $foo = $io->ask($input, $output, $question);
        echo $foo;
    }
}
