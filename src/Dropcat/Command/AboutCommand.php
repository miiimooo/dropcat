<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class AboutCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = '<info>Display the about</info>';

        $this->setName("about")
            ->setDescription("About dropcat")
            ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $style = new OutputFormatterStyle('black', 'green', array('blink', 'bold'));
        $output = new ConsoleOutput();

        $output->getFormatter()->setStyle('meow', $style);
        $io->newLine(1);

        $output->writeln('<meow>

          ____                              __
         / __ \_________  ____  _________ _/ /_
        / / / / ___/ __ \/ __ \/ ___/ __ `/ __/
       / /_/ / /  / /_/ / /_/ / /__/ /_/ / /_
      /_____/_/   \____/ .___/\___/\__,_/\__/
                      /__/

      Dropcat is a deploy tool for Drupal 8 sites, developed by Wunderkraut Sweden. Meow!
      </meow>');
        $io->newLine(1);


    }
}
